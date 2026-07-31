<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\PlansCampaignRecipientImportRows;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

class PlanCampaignRecipientImportRow implements PlansCampaignRecipientImportRows
{
    public function handle(CampaignRecipientImportRowPlanningInputData $input): CampaignRecipientImportRowData
    {
        $recipient = new CampaignRecipientPlanningInputData(
            id: $this->value($input->row, ['id', 'recipient_id']),
            name: $this->value($input->row, ['name', 'full_name', 'recipient_name']),
            mobile: $this->mobile($this->value($input->row, ['mobile', 'phone', 'contact_number'])),
            email: $this->email($this->value($input->row, ['email', 'email_address'])),
            address: $this->value($input->row, ['address', 'location']),
            externalReference: $this->value($input->row, ['external_reference', 'external_id', 'reference']),
            metadata: [
                ...$input->metadata,
                'import_id' => $this->nullableString($input->importId),
                'audience_id' => $this->nullableString($input->audienceId),
                'row_number' => $input->rowNumber,
            ],
        );

        $errors = $this->errors($recipient);

        return new CampaignRecipientImportRowData(
            importId: $this->nullableString($input->importId),
            audienceId: $this->nullableString($input->audienceId),
            rowNumber: $input->rowNumber,
            status: $errors === [] ? 'valid' : 'invalid',
            recipient: $recipient,
            raw: $input->row,
            errors: $errors,
            effects: $this->effects(),
            metadata: [
                'planning_only' => true,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function value(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $this->nullableString(is_scalar($row[$key]) ? (string) $row[$key] : null);
            }
        }

        return null;
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function email(?string $value): ?string
    {
        $value = $this->nullableString($value);

        return $value === null ? null : strtolower($value);
    }

    private function mobile(?string $value): ?string
    {
        $value = $this->nullableString($value);

        return $value === null ? null : str_replace(' ', '', $value);
    }

    /**
     * @return array<string, string>
     */
    private function errors(CampaignRecipientPlanningInputData $recipient): array
    {
        $errors = [];

        if ($recipient->name === null) {
            $errors['name'] = 'Recipient name is required.';
        }

        if ($recipient->mobile === null && $recipient->email === null && $recipient->address === null) {
            $errors['contact'] = 'At least one recipient contact field is required.';
        }

        return $errors;
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'parses_files' => false,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }
}
