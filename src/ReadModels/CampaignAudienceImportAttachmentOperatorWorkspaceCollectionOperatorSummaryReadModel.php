<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData;

class CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel implements BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function fromWorkspaceResult(
        CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData $result,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData {
        $collection = $result->collection;

        return new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData(
            planningKey: $collection->planningKey,
            campaignName: $this->campaignName($result),
            status: $collection->status,
            operatorPosture: $this->operatorPosture($collection->status),
            totalAudiences: $collection->totalAudiences,
            completeAudiences: $collection->completeAudiences,
            attentionRequiredAudiences: $collection->attentionRequiredAudiences,
            emptyAudiences: $collection->emptyAudiences,
            totalImports: $collection->totalImports,
            attachedRows: $collection->attachedRows,
            blockedRows: $collection->blockedRows,
            recipientDelta: $collection->recipientDelta,
            blockerCount: count($collection->blockers),
            blockers: $collection->blockers,
            effects: [
                'operator_workspace_collection_operator_summary' => true,
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
                ...$metadata,
                'source' => 'campaign-audience-import-attachment-operator-workspace-collection-operator-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    private function operatorPosture(string $status): string
    {
        return match ($status) {
            'complete' => 'ready_for_confirmation',
            'attention_required' => 'review_required',
            default => 'no_audience_activity',
        };
    }

    private function campaignName(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData $result): ?string
    {
        $campaignName = $result->metadata['campaign_name'] ?? $result->collection->metadata['campaign_name'] ?? null;

        return is_scalar($campaignName) && trim((string) $campaignName) !== ''
            ? trim((string) $campaignName)
            : null;
    }
}
