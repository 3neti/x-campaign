<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorReadModels;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace implements CampaignAudienceImportAttachmentOperatorWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly BuildsCampaignAudienceImportAttachmentOperatorReadModels $readModel,
    ) {}

    public function overview(
        string $planningKey,
        string $audienceId,
        array $summaries,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceResultData {
        $plan = $this->requirePlan($planningKey);
        $audience = $this->requireAudience($plan, $audienceId);

        $overview = $this->readModel->fromMutationSummaries(
            campaignId: $plan->campaign->id,
            audienceId: $audience->audience->id,
            summaries: $summaries,
            metadata: [
                ...$metadata,
                'planning_key' => $planningKey,
                'campaign_name' => $plan->campaign->name,
                'audience_name' => $audience->audience->name,
            ],
        );

        return new CampaignAudienceImportAttachmentOperatorWorkspaceResultData(
            overview: $overview,
            effects: [
                ...$overview->effects,
                'operator_workspace' => true,
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
                ...$overview->metadata,
                'planning_key' => $planningKey,
                'campaign_name' => $plan->campaign->name,
                'audience_name' => $audience->audience->name,
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

    private function requireAudience(CampaignPlanData $plan, string $audienceId): CampaignAudiencePlanData
    {
        $audienceId = trim($audienceId);

        foreach ($plan->audiences as $audience) {
            if ($audience instanceof CampaignAudiencePlanData && $audience->audience->id === $audienceId) {
                return $audience;
            }
        }

        throw new InvalidArgumentException("Unknown campaign audience plan [{$audienceId}].");
    }
}
