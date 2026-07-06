<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportReviewSummaries;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewRowIssueData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;

class CampaignAudienceImportReviewSummaryReadModel implements BuildsCampaignAudienceImportReviewSummaries
{
    public function fromCollectionPlan(CampaignAudienceImportRowCollectionPlanData $plan): CampaignAudienceImportReviewSummaryData
    {
        $issues = $this->issues($plan);
        $readyForApproval = $plan->totalRows > 0 && $plan->invalidRows === 0;

        return new CampaignAudienceImportReviewSummaryData(
            importId: $plan->importId,
            audienceId: $plan->audienceId,
            status: $this->status($plan, $readyForApproval),
            totalRows: $plan->totalRows,
            validRows: $plan->validRows,
            invalidRows: $plan->invalidRows,
            readyForApproval: $readyForApproval,
            validRowNumbers: $this->rowNumbers($plan, 'valid_row_numbers'),
            invalidRowNumbers: $this->rowNumbers($plan, 'invalid_row_numbers'),
            issues: $issues,
            effects: [
                ...$plan->effects,
                ...$this->effects(),
            ],
            metadata: [
                ...$plan->metadata,
                'source' => 'campaign-audience-import-review-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    private function status(CampaignAudienceImportRowCollectionPlanData $plan, bool $readyForApproval): string
    {
        if ($plan->totalRows === 0) {
            return 'empty';
        }

        return $readyForApproval ? 'ready' : 'review_required';
    }

    /**
     * @return array<int, CampaignAudienceImportReviewRowIssueData>
     */
    private function issues(CampaignAudienceImportRowCollectionPlanData $plan): array
    {
        $issues = [];

        foreach ($plan->rows as $row) {
            if ($row instanceof CampaignRecipientImportRowData && $row->errors !== []) {
                $issues[] = new CampaignAudienceImportReviewRowIssueData(
                    rowNumber: $row->rowNumber,
                    externalReference: $row->recipient->externalReference,
                    errors: $row->errors,
                    metadata: [
                        'status' => $row->status,
                    ],
                );
            }
        }

        return $issues;
    }

    /**
     * @return array<int, int>
     */
    private function rowNumbers(CampaignAudienceImportRowCollectionPlanData $plan, string $key): array
    {
        $numbers = $plan->metadata[$key] ?? [];

        return is_array($numbers) ? array_values(array_filter($numbers, is_int(...))) : [];
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
