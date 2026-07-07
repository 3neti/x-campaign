<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportApprovals;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;

class DecideCampaignAudienceImportApproval implements DecidesCampaignAudienceImportApprovals
{
    public function handle(
        CampaignAudienceImportReviewSummaryData $summary,
        CampaignAudienceImportApprovalDecisionInputData $input,
    ): CampaignAudienceImportApprovalDecisionData {
        $decision = $this->normalizedDecision($input->decision);
        $blockers = $this->blockers($summary, $decision, $input->decision);

        return new CampaignAudienceImportApprovalDecisionData(
            importId: $summary->importId,
            audienceId: $summary->audienceId,
            decision: $this->nullableString($input->decision) ?? '',
            status: $this->status($decision, $blockers),
            decidedBy: $this->nullableString($input->decidedBy),
            reason: $this->nullableString($input->reason),
            readyForApproval: $summary->readyForApproval,
            blockers: $blockers,
            effects: $this->effects(),
            metadata: [
                ...$input->metadata,
                'summary_status' => $summary->status,
                'total_rows' => $summary->totalRows,
                'valid_rows' => $summary->validRows,
                'invalid_rows' => $summary->invalidRows,
                'valid_row_numbers' => $summary->validRowNumbers,
                'invalid_row_numbers' => $summary->invalidRowNumbers,
                'issue_count' => count($summary->issues),
            ],
        );
    }

    private function normalizedDecision(string $decision): ?string
    {
        $decision = strtolower(trim($decision));

        return in_array($decision, ['approve', 'reject'], true) ? $decision : null;
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignAudienceImportReviewSummaryData $summary, ?string $decision, string $rawDecision): array
    {
        if ($decision === null) {
            return ['Unknown audience import approval decision ['.trim($rawDecision).'].'];
        }

        if ($decision === 'reject') {
            return [];
        }

        if ($summary->readyForApproval) {
            return [];
        }

        $blockers = ['Audience import review is not ready for approval.'];

        if ($summary->invalidRows > 0) {
            $blockers[] = $summary->invalidRows === 1
                ? '1 invalid row requires review.'
                : $summary->invalidRows.' invalid rows require review.';
        }

        if ($summary->totalRows === 0) {
            $blockers[] = 'No import rows are available for approval.';
        }

        return $blockers;
    }

    /**
     * @param  array<int, string>  $blockers
     */
    private function status(?string $decision, array $blockers): string
    {
        if ($blockers !== []) {
            return 'blocked';
        }

        return $decision === 'reject' ? 'rejected' : 'approved';
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'decision_only' => true,
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
