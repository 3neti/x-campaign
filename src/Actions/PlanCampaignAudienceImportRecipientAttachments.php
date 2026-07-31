<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

class PlanCampaignAudienceImportRecipientAttachments implements PlansCampaignAudienceImportRecipientAttachments
{
    public function handle(CampaignAudienceImportApprovalWorkspaceResultData $workspaceResult): CampaignAudienceImportRecipientAttachmentPlanData
    {
        $rows = $this->rows($workspaceResult);
        $blockers = $this->blockers($workspaceResult);

        if ($blockers !== []) {
            return $this->blockedPlan($workspaceResult, $rows, $blockers);
        }

        $attachableRows = array_values(array_filter(
            $rows,
            fn (CampaignRecipientImportRowData $row): bool => $row->status === 'valid',
        ));
        $blockedRows = array_values(array_filter(
            $rows,
            fn (CampaignRecipientImportRowData $row): bool => $row->status !== 'valid',
        ));

        $partialBlockers = [];

        if ($blockedRows !== []) {
            $partialBlockers[] = count($blockedRows) === 1
                ? '1 row is not valid for attachment planning.'
                : count($blockedRows).' rows are not valid for attachment planning.';
        }

        return new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: $workspaceResult->collection->importId,
            audienceId: $workspaceResult->collection->audienceId,
            status: $this->statusFor($attachableRows, $blockedRows),
            totalRows: count($rows),
            attachableRows: count($attachableRows),
            blockedRows: count($blockedRows),
            recipients: array_map(
                fn (CampaignRecipientImportRowData $row): CampaignRecipientPlanningInputData => $this->recipientFor($row, $workspaceResult),
                $attachableRows,
            ),
            attachableRowNumbers: array_map(
                fn (CampaignRecipientImportRowData $row): int => $row->rowNumber,
                $attachableRows,
            ),
            blockedRowNumbers: array_map(
                fn (CampaignRecipientImportRowData $row): int => $row->rowNumber,
                $blockedRows,
            ),
            blockers: $partialBlockers,
            effects: $this->effects(),
            metadata: $this->metadata($workspaceResult),
        );
    }

    /**
     * @return array<int, CampaignRecipientImportRowData>
     */
    private function rows(CampaignAudienceImportApprovalWorkspaceResultData $workspaceResult): array
    {
        return array_values(array_filter(
            $workspaceResult->collection->rows,
            fn (mixed $row): bool => $row instanceof CampaignRecipientImportRowData,
        ));
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignAudienceImportApprovalWorkspaceResultData $workspaceResult): array
    {
        $blockers = [];

        if ($workspaceResult->decision->status !== 'approved') {
            $blockers[] = 'Audience import approval is not approved.';
        }

        foreach ($workspaceResult->decision->blockers as $blocker) {
            $blockers[] = $blocker;
        }

        if (! $workspaceResult->summary->readyForApproval) {
            $blockers[] = 'Audience import review summary is not ready for recipient attachment.';
        }

        if ($workspaceResult->summary->invalidRows > 0) {
            $blockers[] = $workspaceResult->summary->invalidRows === 1
                ? '1 invalid row remains unresolved.'
                : $workspaceResult->summary->invalidRows.' invalid rows remain unresolved.';
        }

        if ($workspaceResult->collection->totalRows === 0) {
            $blockers[] = 'No audience import rows are available for recipient attachment planning.';
        }

        return array_values(array_unique($blockers));
    }

    /**
     * @param  array<int, CampaignRecipientImportRowData>  $rows
     * @param  array<int, string>  $blockers
     */
    private function blockedPlan(CampaignAudienceImportApprovalWorkspaceResultData $workspaceResult, array $rows, array $blockers): CampaignAudienceImportRecipientAttachmentPlanData
    {
        return new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: $workspaceResult->collection->importId,
            audienceId: $workspaceResult->collection->audienceId,
            status: 'blocked',
            totalRows: count($rows),
            attachableRows: 0,
            blockedRows: count($rows),
            recipients: [],
            attachableRowNumbers: [],
            blockedRowNumbers: array_map(
                fn (CampaignRecipientImportRowData $row): int => $row->rowNumber,
                $rows,
            ),
            blockers: $blockers,
            effects: $this->effects(),
            metadata: $this->metadata($workspaceResult),
        );
    }

    /**
     * @param  array<int, CampaignRecipientImportRowData>  $attachableRows
     * @param  array<int, CampaignRecipientImportRowData>  $blockedRows
     */
    private function statusFor(array $attachableRows, array $blockedRows): string
    {
        if ($attachableRows === []) {
            return 'blocked';
        }

        if ($blockedRows !== []) {
            return 'partial';
        }

        return 'ready';
    }

    private function recipientFor(CampaignRecipientImportRowData $row, CampaignAudienceImportApprovalWorkspaceResultData $workspaceResult): CampaignRecipientPlanningInputData
    {
        return new CampaignRecipientPlanningInputData(
            id: $row->recipient->id,
            name: $row->recipient->name,
            mobile: $row->recipient->mobile,
            email: $row->recipient->email,
            address: $row->recipient->address,
            externalReference: $row->recipient->externalReference,
            metadata: [
                ...$row->recipient->metadata,
                'import_id' => $workspaceResult->collection->importId,
                'audience_id' => $workspaceResult->collection->audienceId,
                'source_row_number' => $row->rowNumber,
            ],
        );
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'attachment_plan' => true,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(CampaignAudienceImportApprovalWorkspaceResultData $workspaceResult): array
    {
        return [
            ...$workspaceResult->metadata,
            'approval_status' => $workspaceResult->decision->status,
            'summary_status' => $workspaceResult->summary->status,
        ];
    }
}
