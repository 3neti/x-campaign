<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\CampaignRecipientImportRowWorkspace;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRowCollections;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowPlanningInputData;

class PlanCampaignAudienceImportRowCollection implements PlansCampaignAudienceImportRowCollections
{
    public function __construct(
        private readonly CampaignRecipientImportRowWorkspace $rowWorkspace,
    ) {}

    public function handle(string $planningKey, CampaignAudienceImportRowCollectionPlanningInputData $input): CampaignAudienceImportRowCollectionPlanData
    {
        $rows = [];

        foreach ($input->rows as $index => $row) {
            $rows[] = $this->rowWorkspace->plan($planningKey, new CampaignRecipientImportRowPlanningInputData(
                importId: $input->importId,
                audienceId: $input->audienceId,
                rowNumber: $input->startingRowNumber + $index,
                row: $row,
                metadata: $input->metadata,
            ));
        }

        $validRows = $this->rowNumbersForStatus($rows, 'valid');
        $invalidRows = $this->rowNumbersForStatus($rows, 'invalid');

        return new CampaignAudienceImportRowCollectionPlanData(
            importId: $this->nullableString($input->importId),
            audienceId: $this->nullableString($input->audienceId),
            status: $this->status($rows, $invalidRows),
            totalRows: count($rows),
            validRows: count($validRows),
            invalidRows: count($invalidRows),
            rows: $rows,
            effects: [
                'uses_row_workspace' => true,
                'parses_files' => false,
                'persists' => false,
                'queues_jobs' => false,
                'adds_recipients' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$input->metadata,
                'planning_key' => $planningKey,
                'starting_row_number' => $input->startingRowNumber,
                'valid_row_numbers' => $validRows,
                'invalid_row_numbers' => $invalidRows,
            ],
        );
    }

    /**
     * @param  array<int, CampaignRecipientImportRowData>  $rows
     * @return array<int, int>
     */
    private function rowNumbersForStatus(array $rows, string $status): array
    {
        $numbers = [];

        foreach ($rows as $row) {
            if ($row->status === $status) {
                $numbers[] = $row->rowNumber;
            }
        }

        return $numbers;
    }

    /**
     * @param  array<int, CampaignRecipientImportRowData>  $rows
     * @param  array<int, int>  $invalidRows
     */
    private function status(array $rows, array $invalidRows): string
    {
        if ($rows === []) {
            return 'empty';
        }

        return $invalidRows === [] ? 'valid' : 'invalid';
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
