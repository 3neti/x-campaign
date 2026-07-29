<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetRepository;
use LBHurtado\XCampaign\Data\CampaignWorksheetData;
use LBHurtado\XCampaign\Data\CampaignWorksheetRowData;
use LBHurtado\XCampaign\Models\CampaignWorksheet;

class EloquentCampaignWorksheetRepository implements CampaignWorksheetRepository
{
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
            frozenAt: $worksheet->frozen_at,
        );
    }
}
