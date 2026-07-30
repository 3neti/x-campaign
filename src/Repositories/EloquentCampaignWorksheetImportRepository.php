<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetImportRepository;
use LBHurtado\XCampaign\Data\CampaignWorksheetImportData;
use LBHurtado\XCampaign\Models\CampaignWorksheet;
use LBHurtado\XCampaign\Models\CampaignWorksheetImport;
use LBHurtado\XCampaign\Models\CampaignWorksheetImportRow;

class EloquentCampaignWorksheetImportRepository implements CampaignWorksheetImportRepository
{
    public function stage(CampaignWorksheetImportData $import, string $ownerType, string $ownerId): CampaignWorksheetImportData
    {
        return DB::transaction(function () use ($import, $ownerId, $ownerType): CampaignWorksheetImportData {
            $worksheet = $this->worksheet($import->worksheetReference, $ownerType, $ownerId, true, true);
            $record = new CampaignWorksheetImport;
            $record->reference = $import->reference ?? (string) Str::ulid();
            $record->fill([
                'campaign_worksheet_id' => $worksheet->getKey(),
                'status' => 'staged',
                'source_format' => $import->sourceFormat,
                'content_hash' => $import->contentHash,
                'row_count' => $import->rowCount,
                'rows_ciphertext' => $import->stagedRows === []
                    ? ['valid_rows' => $import->validRows, 'validation_errors' => $import->validationErrors]
                    : ['source_headers' => $import->sourceHeaders, 'source_sheet' => $import->sourceSheet],
                'mapping' => $import->mapping,
            ])->save();

            $this->createStagedRows($record, $import->stagedRows);

            return $this->toData($record, (string) $worksheet->reference);
        });
    }

    public function findForOwner(string $worksheetReference, string $importReference, string $ownerType, string $ownerId): ?CampaignWorksheetImportData
    {
        $worksheet = $this->worksheet($worksheetReference, $ownerType, $ownerId);
        $record = $worksheet->imports()->where('reference', $importReference)->first();

        return $record instanceof CampaignWorksheetImport ? $this->toData($record, $worksheetReference) : null;
    }

    public function forOwner(string $worksheetReference, string $ownerType, string $ownerId): array
    {
        $worksheet = $this->worksheet($worksheetReference, $ownerType, $ownerId);

        return $worksheet->imports()
            ->latest('created_at')
            ->get()
            ->map(fn (CampaignWorksheetImport $record): CampaignWorksheetImportData => $this->toData($record, $worksheetReference))
            ->all();
    }

    public function apply(string $worksheetReference, string $importReference, string $ownerType, string $ownerId): CampaignWorksheetImportData
    {
        return DB::transaction(function () use ($worksheetReference, $importReference, $ownerType, $ownerId): CampaignWorksheetImportData {
            $worksheet = $this->worksheet($worksheetReference, $ownerType, $ownerId, true, true);
            $record = $worksheet->imports()->where('reference', $importReference)->lockForUpdate()->first();

            if (! $record instanceof CampaignWorksheetImport) {
                throw new InvalidArgumentException('Campaign worksheet import was not found.');
            }

            if (! in_array($record->status, ['staged', 'applied_with_errors'], true)) {
                throw new InvalidArgumentException('Campaign worksheet import has already been applied or is unavailable.');
            }

            if ($record->rows()->exists()) {
                return $this->applyStagedRows($worksheet, $record, $worksheetReference);
            }

            $payload = $record->rows_ciphertext ?? [];
            $validRows = $payload['valid_rows'] ?? [];
            $errors = $payload['validation_errors'] ?? [];

            if ($errors !== [] || $validRows === []) {
                throw new InvalidArgumentException('Only a fully valid staged import may be applied.');
            }

            $nextOrdinal = (int) $worksheet->rows()->max('ordinal') + 1;
            foreach ($validRows as $row) {
                $worksheet->rows()->create([
                    'reference' => (string) Str::ulid(),
                    'ordinal' => $nextOrdinal++,
                    'beneficiary_ciphertext' => $row['beneficiary'],
                    'amount_minor' => $row['amount_minor'],
                    'currency' => $row['currency'] ?? $worksheet->currency,
                    'delivery_preference' => $row['delivery_preference'] ?? 'manual',
                    'status' => 'draft',
                    'metadata' => ['source' => 'import', 'import_reference' => (string) $record->reference],
                ]);
            }

            $record->status = 'applied';
            $record->save();

            return $this->toData($record->fresh(), $worksheetReference);
        });
    }

    public function replaceUnappliedRows(
        string $worksheetReference,
        string $importReference,
        string $ownerType,
        string $ownerId,
        array $mapping,
        array $rows,
    ): CampaignWorksheetImportData {
        return DB::transaction(function () use (
            $worksheetReference,
            $importReference,
            $ownerType,
            $ownerId,
            $mapping,
            $rows,
        ): CampaignWorksheetImportData {
            $worksheet = $this->worksheet($worksheetReference, $ownerType, $ownerId, true, true);
            $record = $worksheet->imports()
                ->where('reference', $importReference)
                ->lockForUpdate()
                ->first();

            if (! $record instanceof CampaignWorksheetImport || $record->status === 'discarded') {
                throw new InvalidArgumentException('Campaign worksheet import is unavailable.');
            }

            $record->rows()->whereNull('applied_at')->delete();
            $record->mapping = $mapping;
            $record->status = $record->rows()->whereNotNull('applied_at')->exists()
                ? 'applied_with_errors'
                : 'staged';
            $record->save();
            $this->createStagedRows($record, $rows);

            return $this->toData($record->fresh(), $worksheetReference);
        });
    }

    public function discard(
        string $worksheetReference,
        string $importReference,
        string $ownerType,
        string $ownerId,
    ): CampaignWorksheetImportData {
        return DB::transaction(function () use (
            $worksheetReference,
            $importReference,
            $ownerType,
            $ownerId,
        ): CampaignWorksheetImportData {
            $worksheet = $this->worksheet($worksheetReference, $ownerType, $ownerId, true, true);
            $record = $worksheet->imports()
                ->where('reference', $importReference)
                ->lockForUpdate()
                ->first();

            if (! $record instanceof CampaignWorksheetImport || $record->status === 'discarded') {
                throw new InvalidArgumentException('Campaign worksheet import is unavailable.');
            }

            $record->status = 'discarded';
            $record->save();

            return $this->toData($record->fresh(), $worksheetReference);
        });
    }

    private function worksheet(string $reference, string $ownerType, string $ownerId, bool $lock = false, bool $requireDraft = false): CampaignWorksheet
    {
        $query = CampaignWorksheet::query()->where('reference', trim($reference))->where('owner_type', $ownerType)->where('owner_id', $ownerId);
        if ($lock) {
            $query->lockForUpdate();
        }

        $worksheet = $query->first();
        if (! $worksheet instanceof CampaignWorksheet || ($requireDraft && $worksheet->status !== 'draft')) {
            throw new InvalidArgumentException('Only a draft campaign worksheet may be changed.');
        }

        return $worksheet;
    }

    private function toData(CampaignWorksheetImport $record, string $worksheetReference): CampaignWorksheetImportData
    {
        $payload = $record->rows_ciphertext ?? [];
        $stagedRows = $record->rows()
            ->orderBy('source_row')
            ->get()
            ->map(fn (CampaignWorksheetImportRow $row): array => [
                'source_row' => (int) $row->source_row,
                'status' => (string) $row->status,
                'source' => $row->source_ciphertext ?? [],
                'normalized' => $row->normalized_ciphertext,
                'errors' => $row->errors_ciphertext ?? [],
                'applied_at' => $row->applied_at?->format(DATE_ATOM),
            ])
            ->all();
        $validRows = $stagedRows === []
            ? ($payload['valid_rows'] ?? [])
            : collect($stagedRows)
                ->whereIn('status', ['valid', 'applied'])
                ->pluck('normalized')
                ->filter()
                ->values()
                ->all();
        $validationErrors = $stagedRows === []
            ? ($payload['validation_errors'] ?? [])
            : collect($stagedRows)
                ->where('status', 'invalid')
                ->map(fn (array $row): array => [
                    'row' => $row['source_row'],
                    'messages' => $row['errors'],
                ])
                ->values()
                ->all();

        return new CampaignWorksheetImportData(
            reference: (string) $record->reference,
            worksheetReference: $worksheetReference,
            status: (string) $record->status,
            sourceFormat: (string) $record->source_format,
            contentHash: (string) $record->content_hash,
            rowCount: (int) $record->row_count,
            validRows: $validRows,
            validationErrors: $validationErrors,
            mapping: $record->mapping ?? [],
            appliedAt: in_array($record->status, ['applied', 'applied_with_errors'], true)
                && $record->updated_at instanceof DateTimeInterface
                    ? $record->updated_at->format(DATE_ATOM)
                    : null,
            stagedRows: $stagedRows,
            sourceHeaders: $payload['source_headers'] ?? [],
            sourceSheet: $payload['source_sheet'] ?? null,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function createStagedRows(
        CampaignWorksheetImport $record,
        array $rows,
    ): void {
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

    private function applyStagedRows(
        CampaignWorksheet $worksheet,
        CampaignWorksheetImport $record,
        string $worksheetReference,
    ): CampaignWorksheetImportData {
        $rows = $record->rows()
            ->where('status', 'valid')
            ->whereNull('applied_at')
            ->lockForUpdate()
            ->orderBy('source_row')
            ->get();

        if ($rows->isEmpty()) {
            throw new InvalidArgumentException('No valid unapplied beneficiaries are available.');
        }

        $nextOrdinal = (int) $worksheet->rows()->max('ordinal') + 1;
        foreach ($rows as $row) {
            $normalized = $row->normalized_ciphertext ?? [];
            $worksheet->rows()->create([
                'reference' => (string) Str::ulid(),
                'ordinal' => $nextOrdinal++,
                'beneficiary_ciphertext' => $normalized['beneficiary'] ?? [],
                'amount_minor' => $normalized['amount_minor'] ?? 0,
                'currency' => $normalized['currency'] ?? $worksheet->currency,
                'delivery_preference' => $normalized['delivery_preference'] ?? 'manual',
                'status' => 'draft',
                'metadata' => [
                    'source' => 'import',
                    'import_reference' => (string) $record->reference,
                    'source_row' => (int) $row->source_row,
                ],
            ]);
            $row->forceFill([
                'status' => 'applied',
                'applied_at' => now(),
            ])->save();
        }

        $record->status = $record->rows()->where('status', 'invalid')->exists()
            ? 'applied_with_errors'
            : 'applied';
        $record->save();

        return $this->toData($record->fresh(), $worksheetReference);
    }
}
