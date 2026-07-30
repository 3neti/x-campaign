<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetRepository;
use LBHurtado\XCampaign\Data\CampaignWorksheetData;
use LBHurtado\XCampaign\Data\CampaignWorksheetRowData;
use LBHurtado\XCampaign\Data\CampaignWorksheetSummaryData;
use LBHurtado\XCampaign\Models\CampaignWorksheet;
use LBHurtado\XCampaign\Models\CampaignWorksheetIntake;
use LBHurtado\XCampaign\Services\CampaignWorksheetManifestHasher;

class EloquentCampaignWorksheetRepository implements CampaignWorksheetRepository
{
    public function __construct(
        private readonly CampaignWorksheetManifestHasher $hasher,
    ) {}

    public function put(CampaignWorksheetData $worksheet): CampaignWorksheetData
    {
        $this->assertValid($worksheet);

        return DB::transaction(function () use ($worksheet): CampaignWorksheetData {
            $reference = $worksheet->reference ?? (string) Str::ulid();
            $record = CampaignWorksheet::query()
                ->where('reference', $reference)
                ->lockForUpdate()
                ->first();

            if ($record === null) {
                $record = new CampaignWorksheet;
                $record->reference = $reference;
            } elseif (
                $record->owner_type !== $worksheet->ownerType
                || $record->owner_id !== $worksheet->ownerId
            ) {
                throw new InvalidArgumentException('Campaign worksheet reference is already owned by another principal.');
            }

            $record->fill([
                'owner_type' => $worksheet->ownerType,
                'owner_id' => $worksheet->ownerId,
                'profile' => $worksheet->profile,
                'name' => $worksheet->name,
                'currency' => $worksheet->currency,
                'status' => $worksheet->status,
                'pay_code_template_reference' => $worksheet->payCodeTemplateReference,
                'fulfillment_mode' => $worksheet->fulfillmentMode,
                'delivery_plan' => $worksheet->deliveryPlan,
                'metadata' => $worksheet->metadata,
                'rows_hash' => $worksheet->rowsHash,
                'instruction_blueprint_ciphertext' => $worksheet->instructionBlueprint,
                'instruction_blueprint_hash' => $worksheet->instructionBlueprintHash,
                'instruction_blueprint_schema' => $worksheet->instructionBlueprintSchema,
                'instruction_blueprint_revision' => $worksheet->instructionBlueprintRevision,
                'manifest_hash' => $worksheet->manifestHash,
                'frozen_at' => $worksheet->frozenAt,
            ])->save();

            $record->rows()->delete();

            foreach ($worksheet->rows as $row) {
                $rowRecord = $record->rows()->make([
                    'ordinal' => $row->ordinal,
                    'beneficiary_ciphertext' => $row->beneficiary,
                    'amount_minor' => $row->amountMinor,
                    'currency' => $row->currency,
                    'delivery_preference' => $row->deliveryPreference,
                    'status' => $row->status,
                    'metadata' => $row->metadata,
                ]);
                $rowRecord->reference = $row->reference ?? (string) Str::ulid();
                $rowRecord->save();
            }

            return $this->toData($record->fresh('rows'));
        });
    }

    public function findForOwner(string $reference, string $ownerType, string $ownerId): ?CampaignWorksheetData
    {
        $record = CampaignWorksheet::query()
            ->where('reference', trim($reference))
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->with('rows')
            ->first();

        return $record instanceof CampaignWorksheet ? $this->toData($record) : null;
    }

    public function appendRow(
        string $reference,
        string $ownerType,
        string $ownerId,
        CampaignWorksheetRowData $row,
    ): CampaignWorksheetData {
        return $this->appendRows($reference, $ownerType, $ownerId, [$row]);
    }

    public function appendRows(
        string $reference,
        string $ownerType,
        string $ownerId,
        array $rows,
    ): CampaignWorksheetData {
        foreach ($rows as $row) {
            if (! $row instanceof CampaignWorksheetRowData || $row->amountMinor < 1) {
                throw new InvalidArgumentException('Campaign worksheet row amounts must be positive.');
            }
        }

        return DB::transaction(function () use ($reference, $ownerId, $ownerType, $rows): CampaignWorksheetData {
            $worksheet = CampaignWorksheet::query()
                ->where('reference', trim($reference))
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->lockForUpdate()
                ->first();

            if (! $worksheet instanceof CampaignWorksheet) {
                throw new InvalidArgumentException('Campaign worksheet was not found for this owner.');
            }

            if ($worksheet->status !== 'draft') {
                throw new InvalidArgumentException('Only a draft campaign worksheet may be changed.');
            }

            $nextOrdinal = (int) $worksheet->rows()->max('ordinal') + 1;

            foreach ($rows as $row) {
                $rowRecord = $worksheet->rows()->make([
                    'ordinal' => max((int) $row->ordinal, $nextOrdinal),
                    'beneficiary_ciphertext' => $row->beneficiary,
                    'amount_minor' => $row->amountMinor,
                    'currency' => $row->currency,
                    'delivery_preference' => $row->deliveryPreference,
                    'status' => $row->status,
                    'metadata' => $row->metadata,
                ]);
                $rowRecord->reference = $row->reference ?? (string) Str::ulid();
                $rowRecord->save();
                $nextOrdinal++;
            }

            return $this->toData($worksheet->fresh('rows'));
        });
    }

    public function freeze(string $reference, string $ownerType, string $ownerId): CampaignWorksheetData
    {
        return DB::transaction(function () use ($reference, $ownerType, $ownerId): CampaignWorksheetData {
            $worksheet = CampaignWorksheet::query()
                ->where('reference', trim($reference))
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->with('rows')
                ->lockForUpdate()
                ->first();

            if (! $worksheet instanceof CampaignWorksheet) {
                throw new InvalidArgumentException('Campaign worksheet was not found for this owner.');
            }

            if ($worksheet->status !== 'draft') {
                throw new InvalidArgumentException('Only a draft campaign worksheet may be frozen.');
            }

            if ($worksheet->rows->isEmpty()) {
                throw new InvalidArgumentException('A campaign worksheet needs at least one beneficiary before it can be frozen.');
            }

            $manifest = $worksheet->rows->map(fn ($row): array => [
                'ordinal' => (int) $row->ordinal,
                'beneficiary' => $row->beneficiary_ciphertext,
                'amount_minor' => (int) $row->amount_minor,
                'currency' => (string) $row->currency,
                'delivery_preference' => $row->delivery_preference,
            ])->all();
            $rowsHash = $this->hasher->hash($manifest);
            $blueprint = $worksheet->instruction_blueprint_ciphertext ?? [];
            $blueprintSchema = $worksheet->instruction_blueprint_schema ?? 'x-campaign.instruction-blueprint.v1';
            $blueprintHash = $worksheet->instruction_blueprint_hash ?? $this->hasher->hash($blueprint);
            $manifestHash = $this->hasher->hash([
                'schema' => 'x-campaign.worksheet-manifest.v2',
                'rows_hash' => $rowsHash,
                'instruction_blueprint_hash' => $blueprintHash,
                'instruction_blueprint_schema' => $blueprintSchema,
                'currency' => (string) $worksheet->currency,
                'fulfillment_mode' => (string) $worksheet->fulfillment_mode,
                'delivery_plan' => $worksheet->delivery_plan ?? [],
                'pay_code_template_reference' => $worksheet->pay_code_template_reference,
            ]);

            $worksheet->forceFill([
                'status' => 'awaiting_authorization',
                'rows_hash' => $rowsHash,
                'instruction_blueprint_ciphertext' => $blueprint,
                'instruction_blueprint_hash' => $blueprintHash,
                'instruction_blueprint_schema' => $blueprintSchema,
                'manifest_hash' => $manifestHash,
                'frozen_at' => now(),
            ])->save();

            return $this->toData($worksheet->fresh('rows'));
        });
    }

    public function updateInstructionBlueprint(
        string $reference,
        string $ownerType,
        string $ownerId,
        array $blueprint,
        string $schema,
        int $expectedRevision,
    ): CampaignWorksheetData {
        return DB::transaction(function () use ($reference, $ownerType, $ownerId, $blueprint, $schema, $expectedRevision): CampaignWorksheetData {
            $worksheet = CampaignWorksheet::query()
                ->where('reference', trim($reference))
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->lockForUpdate()
                ->first();

            if (! $worksheet instanceof CampaignWorksheet) {
                throw new InvalidArgumentException('Campaign worksheet was not found for this owner.');
            }

            if ($worksheet->status !== 'draft') {
                throw new InvalidArgumentException('Only a draft campaign worksheet blueprint may be changed.');
            }

            if ((int) $worksheet->instruction_blueprint_revision !== $expectedRevision) {
                throw new InvalidArgumentException('The campaign Pay Code blueprint changed in another session. Refresh before saving.');
            }

            $normalizedSchema = trim($schema);
            if ($normalizedSchema === '') {
                throw new InvalidArgumentException('Campaign instruction blueprint schema is required.');
            }

            $worksheet->forceFill([
                'instruction_blueprint_ciphertext' => $this->hasher->canonicalize($blueprint),
                'instruction_blueprint_hash' => $this->hasher->hash($blueprint),
                'instruction_blueprint_schema' => $normalizedSchema,
                'instruction_blueprint_revision' => $expectedRevision + 1,
                'manifest_hash' => null,
            ])->save();

            return $this->toData($worksheet->fresh('rows'));
        });
    }

    public function deleteDraft(string $reference, string $ownerType, string $ownerId): void
    {
        DB::transaction(function () use ($reference, $ownerType, $ownerId): void {
            $worksheet = CampaignWorksheet::query()
                ->where('reference', trim($reference))
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->lockForUpdate()
                ->first();

            if (! $worksheet instanceof CampaignWorksheet) {
                throw new InvalidArgumentException('Campaign worksheet was not found for this owner.');
            }

            if ($worksheet->status !== 'draft') {
                throw new InvalidArgumentException('Only a draft campaign worksheet may be deleted.');
            }

            $worksheet->imports()
                ->get()
                ->each(fn ($import) => $import->rows()->delete());
            $worksheet->imports()->delete();
            $worksheet->rows()->delete();

            CampaignWorksheetIntake::query()
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->where('status', 'converted')
                ->where('converted_worksheet_reference', $worksheet->reference)
                ->update(['status' => 'superseded']);

            $worksheet->delete();
        });
    }

    /**
     * @return array<int, CampaignWorksheetSummaryData>
     */
    public function summariesForOwner(string $ownerType, string $ownerId): array
    {
        return CampaignWorksheet::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->withCount('rows')
            ->withSum('rows', 'amount_minor')
            ->latest('updated_at')
            ->get()
            ->map(fn (CampaignWorksheet $worksheet): CampaignWorksheetSummaryData => new CampaignWorksheetSummaryData(
                reference: (string) $worksheet->reference,
                profile: (string) $worksheet->profile,
                name: (string) $worksheet->name,
                currency: (string) $worksheet->currency,
                status: (string) $worksheet->status,
                payCodeTemplateReference: $worksheet->pay_code_template_reference,
                fulfillmentMode: (string) $worksheet->fulfillment_mode,
                deliveryPlan: $worksheet->delivery_plan ?? [],
                beneficiaryCount: (int) $worksheet->rows_count,
                principalMinor: (int) ($worksheet->rows_sum_amount_minor ?? 0),
                updatedAt: $this->timestamp($worksheet->updated_at),
            ))
            ->all();
    }

    private function assertValid(CampaignWorksheetData $worksheet): void
    {
        if (! in_array($worksheet->profile, ['payroll', 'assistance'], true)) {
            throw new InvalidArgumentException('Campaign worksheet profile must be payroll or assistance.');
        }

        if (! in_array($worksheet->fulfillmentMode, ['pay_code_distribution', 'direct_bank_transfer'], true)) {
            throw new InvalidArgumentException('Campaign worksheet fulfillment mode is not supported.');
        }

        foreach ($worksheet->rows as $row) {
            if ($row->amountMinor < 1) {
                throw new InvalidArgumentException('Campaign worksheet row amounts must be positive.');
            }
        }
    }

    private function timestamp(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return null;
    }

    private function toData(CampaignWorksheet $worksheet): CampaignWorksheetData
    {
        return new CampaignWorksheetData(
            reference: (string) $worksheet->reference,
            ownerType: (string) $worksheet->owner_type,
            ownerId: (string) $worksheet->owner_id,
            profile: (string) $worksheet->profile,
            name: (string) $worksheet->name,
            currency: (string) $worksheet->currency,
            status: (string) $worksheet->status,
            payCodeTemplateReference: $worksheet->pay_code_template_reference,
            fulfillmentMode: (string) $worksheet->fulfillment_mode,
            deliveryPlan: $worksheet->delivery_plan ?? [],
            rows: $worksheet->rows
                ->map(fn ($row): CampaignWorksheetRowData => new CampaignWorksheetRowData(
                    reference: (string) $row->reference,
                    ordinal: (int) $row->ordinal,
                    beneficiary: $row->beneficiary_ciphertext,
                    amountMinor: (int) $row->amount_minor,
                    currency: (string) $row->currency,
                    deliveryPreference: $row->delivery_preference,
                    status: (string) $row->status,
                    metadata: $row->metadata ?? [],
                ))
                ->all(),
            metadata: $worksheet->metadata ?? [],
            rowsHash: $worksheet->rows_hash,
            instructionBlueprint: $worksheet->instruction_blueprint_ciphertext ?? [],
            instructionBlueprintHash: $worksheet->instruction_blueprint_hash,
            instructionBlueprintSchema: $worksheet->instruction_blueprint_schema,
            instructionBlueprintRevision: (int) $worksheet->instruction_blueprint_revision,
            manifestHash: $worksheet->manifest_hash,
            frozenAt: $worksheet->frozen_at,
        );
    }
}
