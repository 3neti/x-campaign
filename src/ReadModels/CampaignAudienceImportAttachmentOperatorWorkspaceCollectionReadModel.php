<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;

class CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel implements BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections
{
    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     * @param  array<string, mixed>  $metadata
     */
    public function fromWorkspaceResults(
        string $planningKey,
        array $results,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData {
        $results = array_values(array_filter(
            $results,
            fn (mixed $result): bool => $result instanceof CampaignAudienceImportAttachmentOperatorWorkspaceResultData,
        ));

        return new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData(
            planningKey: $planningKey,
            status: $this->status($results),
            totalAudiences: count($results),
            completeAudiences: $this->countByStatus($results, 'complete'),
            attentionRequiredAudiences: $this->countByStatus($results, 'attention_required'),
            emptyAudiences: $this->countByStatus($results, 'empty'),
            totalImports: $this->sum($results, 'totalImports'),
            attachedRows: $this->sum($results, 'attachedRows'),
            blockedRows: $this->sum($results, 'blockedRows'),
            recipientDelta: $this->sum($results, 'recipientDelta'),
            results: $results,
            blockers: $this->blockers($results),
            effects: [
                'operator_workspace_collection' => true,
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
                ...$metadata,
                'source' => 'campaign-audience-import-attachment-operator-workspace-collection-read-model',
                'read_only' => true,
            ],
        );
    }

    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     */
    private function status(array $results): string
    {
        if ($results === []) {
            return 'empty';
        }

        if ($this->countByStatus($results, 'attention_required') > 0) {
            return 'attention_required';
        }

        return 'complete';
    }

    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     */
    private function countByStatus(array $results, string $status): int
    {
        return count(array_filter(
            $results,
            fn (CampaignAudienceImportAttachmentOperatorWorkspaceResultData $result): bool => $result->overview->status === $status,
        ));
    }

    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     */
    private function sum(array $results, string $property): int
    {
        return array_reduce(
            $results,
            fn (int $total, CampaignAudienceImportAttachmentOperatorWorkspaceResultData $result): int => $total + (int) $result->overview->{$property},
            0,
        );
    }

    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     * @return array<int, string>
     */
    private function blockers(array $results): array
    {
        return array_values(array_unique(array_merge(...array_map(
            fn (CampaignAudienceImportAttachmentOperatorWorkspaceResultData $result): array => $result->overview->blockers,
            $results,
        ))));
    }
}
