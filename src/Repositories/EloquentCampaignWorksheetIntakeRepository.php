<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetIntakeRepository;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetRepository;
use LBHurtado\XCampaign\Data\CampaignWorksheetData;
use LBHurtado\XCampaign\Data\CampaignWorksheetIntakeData;
use LBHurtado\XCampaign\Models\CampaignWorksheet;
use LBHurtado\XCampaign\Models\CampaignWorksheetImport;
use LBHurtado\XCampaign\Models\CampaignWorksheetIntake;
use LBHurtado\XCampaign\Models\CampaignWorksheetIntakeRow;

class EloquentCampaignWorksheetIntakeRepository implements CampaignWorksheetIntakeRepository
{
    public function __construct(private readonly CampaignWorksheetRepository $worksheets) {}

    public function stage(CampaignWorksheetIntakeData $intake): CampaignWorksheetIntakeData
    {
        return DB::transaction(function () use ($intake): CampaignWorksheetIntakeData {
            $duplicate = $this->ownerQuery($intake->ownerType, $intake->ownerId)
                ->where('content_hash', $intake->contentHash)
                ->whereIn('status', ['staged', 'converted'])
                ->lockForUpdate()
                ->latest('created_at')
                ->first();
            if ($duplicate instanceof CampaignWorksheetIntake) {
                return $this->toData($duplicate);
            }

            $record = new CampaignWorksheetIntake;
            $record->reference = $intake->reference ?? (string) Str::ulid();
            $record->fill([
                'owner_type' => $intake->ownerType,
                'owner_id' => $intake->ownerId,
                'status' => 'staged',
                'source_name_ciphertext' => $intake->sourceName,
                'source_format' => $intake->sourceFormat,
                'content_hash' => $intake->contentHash,
                'row_count' => $intake->rowCount,
                'source_manifest_ciphertext' => [
                    'headers' => $intake->sourceHeaders,
                    'sheet' => $intake->sourceSheet,
                ],
                'mapping' => $intake->mapping,
                'suggestion' => $intake->suggestion,
            ])->save();
            $this->createRows($record, $intake->rows);

            return $this->toData($record->fresh());
        });
    }

    public function findForOwner(string $reference, string $ownerType, string $ownerId): ?CampaignWorksheetIntakeData
    {
        $record = $this->ownerQuery($ownerType, $ownerId)
            ->where('reference', trim($reference))
            ->first();

        return $record instanceof CampaignWorksheetIntake ? $this->toData($record) : null;
    }

    public function activeForOwner(string $ownerType, string $ownerId): ?CampaignWorksheetIntakeData
    {
        $record = $this->ownerQuery($ownerType, $ownerId)
            ->where('status', 'staged')
            ->latest('created_at')
            ->first();

        return $record instanceof CampaignWorksheetIntake ? $this->toData($record) : null;
    }

    public function duplicateForOwner(string $contentHash, string $ownerType, string $ownerId): ?CampaignWorksheetIntakeData
    {
        $record = $this->ownerQuery($ownerType, $ownerId)
            ->where('content_hash', $contentHash)
            ->whereIn('status', ['staged', 'converted'])
            ->latest('created_at')
            ->first();

        return $record instanceof CampaignWorksheetIntake ? $this->toData($record) : null;
    }

    public function replaceReview(
        string $reference,
        string $ownerType,
        string $ownerId,
        array $mapping,
        array $suggestion,
        array $rows,
    ): CampaignWorksheetIntakeData {
        return DB::transaction(function () use ($reference, $ownerType, $ownerId, $mapping, $suggestion, $rows): CampaignWorksheetIntakeData {
            $record = $this->lockedStaged($reference, $ownerType, $ownerId);
            $record->rows()->delete();
            $record->forceFill(['mapping' => $mapping, 'suggestion' => $suggestion])->save();
            $this->createRows($record, $rows);

            return $this->toData($record->fresh());
        });
    }

    public function convert(
        string $reference,
        string $ownerType,
        string $ownerId,
        CampaignWorksheetData $worksheet,
        array $includedSourceRows,
    ): CampaignWorksheetData {
        return DB::transaction(function () use ($reference, $ownerType, $ownerId, $worksheet, $includedSourceRows): CampaignWorksheetData {
            $intake = $this->lockedForOwner($reference, $ownerType, $ownerId);
            if ($intake->status === 'converted' && is_string($intake->converted_worksheet_reference)) {
                return $this->worksheets->findForOwner(
                    $intake->converted_worksheet_reference,
                    $ownerType,
                    $ownerId,
                ) ?? throw new InvalidArgumentException('Converted campaign worksheet could not be read.');
            }
            if ($intake->status !== 'staged') {
                throw new InvalidArgumentException('Campaign intake is unavailable.');
            }
            if ($worksheet->ownerType !== $ownerType || $worksheet->ownerId !== $ownerId) {
                throw new InvalidArgumentException('Campaign intake and worksheet owner must match.');
            }

            $rows = $intake->rows()
                ->where('status', 'valid')
                ->whereIn('source_row', array_values(array_unique($includedSourceRows)))
                ->orderBy('source_row')
                ->lockForUpdate()
                ->get();
            if ($rows->isEmpty()) {
                throw new InvalidArgumentException('Select at least one valid beneficiary.');
            }

            $campaign = new CampaignWorksheet;
            $campaign->reference = $worksheet->reference ?? (string) Str::ulid();
            $campaign->fill([
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'profile' => $worksheet->profile,
                'name' => $worksheet->name,
                'currency' => $worksheet->currency,
                'status' => 'draft',
                'fulfillment_mode' => $worksheet->fulfillmentMode,
                'delivery_plan' => $worksheet->deliveryPlan,
                'metadata' => [...$worksheet->metadata, 'source' => 'campaign_intake', 'intake_reference' => (string) $intake->reference],
            ])->save();

            $import = new CampaignWorksheetImport;
            $import->reference = (string) Str::ulid();
            $import->fill([
                'campaign_worksheet_id' => $campaign->getKey(),
                'status' => $intake->rows()->where('status', 'invalid')->exists() ? 'applied_with_errors' : 'applied',
                'source_format' => $intake->source_format,
                'content_hash' => $intake->content_hash,
                'row_count' => $intake->row_count,
                'rows_ciphertext' => $intake->source_manifest_ciphertext,
                'mapping' => $intake->mapping,
            ])->save();

            foreach ($rows as $index => $row) {
                $normalized = $row->normalized_ciphertext ?? [];
                $campaign->rows()->create([
                    'reference' => (string) Str::ulid(),
                    'ordinal' => $index + 1,
                    'beneficiary_ciphertext' => $normalized['beneficiary'] ?? [],
                    'amount_minor' => $normalized['amount_minor'] ?? 0,
                    'currency' => $normalized['currency'] ?? $worksheet->currency,
                    'delivery_preference' => $normalized['delivery_preference'] ?? 'manual',
                    'status' => 'draft',
                    'metadata' => [
                        'source' => 'campaign_intake',
                        'import_reference' => (string) $import->reference,
                        'source_row' => (int) $row->source_row,
                    ],
                ]);
            }

            $intake->forceFill([
                'status' => 'converted',
                'converted_worksheet_reference' => (string) $campaign->reference,
                'converted_at' => now(),
            ])->save();

            return $this->worksheets
                ->findForOwner((string) $campaign->reference, $ownerType, $ownerId)
                ?? throw new InvalidArgumentException('Converted campaign worksheet could not be read.');
        }, attempts: 3);
    }

    public function discard(string $reference, string $ownerType, string $ownerId): void
    {
        DB::transaction(function () use ($reference, $ownerType, $ownerId): void {
            $record = $this->lockedStaged($reference, $ownerType, $ownerId);
            $record->rows()->delete();
            $record->delete();
        });
    }

    private function ownerQuery(string $ownerType, string $ownerId)
    {
        return CampaignWorksheetIntake::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId);
    }

    private function lockedStaged(string $reference, string $ownerType, string $ownerId): CampaignWorksheetIntake
    {
        $record = $this->lockedForOwner($reference, $ownerType, $ownerId);
        if (! $record instanceof CampaignWorksheetIntake || $record->status !== 'staged') {
            throw new InvalidArgumentException('Campaign intake is unavailable.');
        }

        return $record;
    }

    private function lockedForOwner(string $reference, string $ownerType, string $ownerId): CampaignWorksheetIntake
    {
        $record = $this->ownerQuery($ownerType, $ownerId)
            ->where('reference', trim($reference))
            ->lockForUpdate()
            ->first();
        if (! $record instanceof CampaignWorksheetIntake) {
            throw new InvalidArgumentException('Campaign intake is unavailable.');
        }

        return $record;
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function createRows(CampaignWorksheetIntake $record, array $rows): void
    {
        foreach ($rows as $row) {
            $record->rows()->create([
                'source_row' => (int) ($row['source_row'] ?? 0),
                'status' => (string) ($row['status'] ?? 'invalid'),
                'source_ciphertext' => (array) ($row['source'] ?? []),
                'normalized_ciphertext' => $row['normalized'] ?? null,
                'errors_ciphertext' => (array) ($row['errors'] ?? []),
            ]);
        }
    }

    private function toData(CampaignWorksheetIntake $record): CampaignWorksheetIntakeData
    {
        $manifest = $record->source_manifest_ciphertext ?? [];

        return new CampaignWorksheetIntakeData(
            reference: (string) $record->reference,
            ownerType: (string) $record->owner_type,
            ownerId: (string) $record->owner_id,
            status: (string) $record->status,
            sourceName: (string) $record->source_name_ciphertext,
            sourceFormat: (string) $record->source_format,
            contentHash: (string) $record->content_hash,
            rowCount: (int) $record->row_count,
            sourceHeaders: $manifest['headers'] ?? [],
            sourceSheet: $manifest['sheet'] ?? null,
            mapping: $record->mapping ?? [],
            suggestion: $record->suggestion ?? [],
            rows: $record->rows()->orderBy('source_row')->get()->map(
                fn (CampaignWorksheetIntakeRow $row): array => [
                    'source_row' => (int) $row->source_row,
                    'status' => (string) $row->status,
                    'source' => $row->source_ciphertext ?? [],
                    'normalized' => $row->normalized_ciphertext,
                    'errors' => $row->errors_ciphertext ?? [],
                ],
            )->all(),
            convertedWorksheetReference: $record->converted_worksheet_reference,
            convertedAt: $this->timestamp($record->converted_at),
        );
    }

    private function timestamp(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : null;
    }
}
