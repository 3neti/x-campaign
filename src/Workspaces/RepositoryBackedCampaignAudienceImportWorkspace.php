<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImports;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class RepositoryBackedCampaignAudienceImportWorkspace implements CampaignAudienceImportWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly PlansCampaignAudienceImports $planner,
    ) {}

    public function plan(string $planningKey, CampaignAudienceImportPlanningInputData $input): CampaignAudienceImportPlanData
    {
        $plan = $this->requirePlan($planningKey);
        $audience = $this->requireAudience($plan, $input->audienceId);

        $import = $this->planner->handle($input);

        return new CampaignAudienceImportPlanData(
            import: $import->import,
            effects: [
                ...$import->effects,
                'integrates_repository' => true,
                'parses_files' => false,
                'persists' => false,
                'queues_jobs' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$import->metadata,
                'planning_key' => $planningKey,
                'audience_name' => $audience->audience->name,
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

    private function requireAudience(CampaignPlanData $plan, ?string $audienceId): CampaignAudiencePlanData
    {
        $audienceId = trim((string) $audienceId);

        foreach ($plan->audiences as $audience) {
            if ($audience instanceof CampaignAudiencePlanData && $audience->audience->id === $audienceId) {
                return $audience;
            }
        }

        throw new InvalidArgumentException("Unknown campaign audience plan [{$audienceId}].");
    }
}
