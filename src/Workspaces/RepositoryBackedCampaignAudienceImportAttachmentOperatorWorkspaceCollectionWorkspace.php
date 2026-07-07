<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace implements CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections $collectionReadModel,
    ) {}

    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     * @param  array<string, mixed>  $metadata
     */
    public function overview(
        string $planningKey,
        array $results,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData {
        $plan = $this->requirePlan($planningKey);

        $collection = $this->collectionReadModel->fromWorkspaceResults(
            planningKey: $planningKey,
            results: $results,
            metadata: [
                ...$metadata,
                'planning_key' => $planningKey,
                'campaign_name' => $plan->campaign->name,
            ],
        );

        return new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData(
            collection: $collection,
            effects: [
                ...$collection->effects,
                'operator_workspace_collection_workspace' => true,
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
                ...$collection->metadata,
                'planning_key' => $planningKey,
                'campaign_name' => $plan->campaign->name,
                'read_only' => true,
            ],
        );
    }

    private function requirePlan(string $planningKey): CampaignPlanData
    {
        $plan = $this->repository->get($planningKey);

        if (! $plan instanceof CampaignPlanData) {
            throw new InvalidArgumentException("Unknown campaign planning key [{$planningKey}].");
        }

        return $plan;
    }
}
