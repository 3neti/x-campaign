<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData;

class CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel implements BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries
{
    public function fromWorkspaceResult(CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData $result): CampaignAudienceImportRecipientAttachmentMutationSummaryData
    {
        return new CampaignAudienceImportRecipientAttachmentMutationSummaryData(
            importId: $result->mutation->importId,
            audienceId: $result->mutation->audienceId,
            status: $this->status($result),
            decisionStatus: $result->decision->status,
            mutationStatus: $result->mutation->status,
            totalRows: $result->workspace->attachment->totalRows,
            attachableRows: $result->workspace->attachment->attachableRows,
            blockedRows: $result->workspace->attachment->blockedRows,
            attachedRows: $result->mutation->attachedRows,
            skippedRows: $result->mutation->skippedRows,
            beforeRecipientCount: $result->mutation->beforeRecipientCount,
            afterRecipientCount: $result->mutation->afterRecipientCount,
            recipientDelta: max(0, $result->mutation->afterRecipientCount - $result->mutation->beforeRecipientCount),
            readyForMutation: $result->decision->readyForMutation && $result->mutation->status === 'attached',
            blockers: $this->blockers($result),
            effects: [
                'recipient_attachment_mutation_summary' => true,
                'read_only' => true,
                'persists' => false,
                'uses_database' => false,
                'queues_jobs' => false,
                'adds_recipients' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$result->metadata,
                'source' => 'campaign-audience-import-recipient-attachment-mutation-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    private function status(CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData $result): string
    {
        if ($result->decision->status === 'deferred') {
            return 'deferred';
        }

        if ($result->mutation->status === 'attached') {
            return 'attached';
        }

        return 'blocked';
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData $result): array
    {
        return array_values(array_unique(array_filter([
            ...$result->workspace->attachment->blockers,
            ...$result->decision->blockers,
            ...$result->mutation->blockers,
        ], is_string(...))));
    }
}
