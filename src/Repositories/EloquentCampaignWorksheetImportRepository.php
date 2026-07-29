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

class EloquentCampaignWorksheetImportRepository implements CampaignWorksheetImportRepository
{
    public function stage(CampaignWorksheetImportData $import, string $ownerType, string $ownerId): CampaignWorksheetImportData
    {
        return DB::transaction(function () use ($import, $ownerId, $ownerType): CampaignWorksheetImportData {
            $worksheet = $this->worksheet($import->worksheetReference, $ownerType, $ownerId, true);
            $record = new CampaignWorksheetImport;
            $record->reference = $import->reference ?? (string) Str::ulid();
            $record->fill([
                'campaign_worksheet_id' => $worksheet->getKey(),
                'status' => 'staged',
                'source_format' => $import->sourceFormat,
                'content_hash' => $import->contentHash,
                'row_count' => $import->rowCount,
                'rows_ciphertext' => ['valid_rows' => $import->validRows, 'validation_errors' => $import->validationErrors],
                'mapping' => $import->mapping,
            ])->save();

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
            $worksheet = $this->worksheet($worksheetReference, $ownerType, $ownerId, true);
            $record = $worksheet->imports()->where('reference', $importReference)->lockForUpdate()->first();

            if (! $record instanceof CampaignWorksheetImport) {
                throw new InvalidArgumentException('Campaign worksheet import was not found.');
            }

            if ($record->status !== 'staged') {
                throw new InvalidArgumentException('Campaign worksheet import has already been applied or is unavailable.');
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

    private function worksheet(string $reference, string $ownerType, string $ownerId, bool $lock = false): CampaignWorksheet
    {
        $query = CampaignWorksheet::query()->where('reference', trim($reference))->where('owner_type', $ownerType)->where('owner_id', $ownerId);
        if ($lock) {
            $query->lockForUpdate();
        }

        $worksheet = $query->first();
        if (! $worksheet instanceof CampaignWorksheet || $worksheet->status !== 'draft') {
            throw new InvalidArgumentException('Only a draft campaign worksheet may be changed.');
        }

        return $worksheet;
    }

    private function toData(CampaignWorksheetImport $record, string $worksheetReference): CampaignWorksheetImportData
    {
        $payload = $record->rows_ciphertext ?? [];

        return new CampaignWorksheetImportData(
            reference: (string) $record->reference,
            worksheetReference: $worksheetReference,
            status: (string) $record->status,
            sourceFormat: (string) $record->source_format,
            contentHash: (string) $record->content_hash,
            rowCount: (int) $record->row_count,
            validRows: $payload['valid_rows'] ?? [],
            validationErrors: $payload['validation_errors'] ?? [],
            mapping: $record->mapping ?? [],
            appliedAt: $record->status === 'applied' && $record->updated_at instanceof DateTimeInterface ? $record->updated_at->format(DATE_ATOM) : null,
        );
    }
}
