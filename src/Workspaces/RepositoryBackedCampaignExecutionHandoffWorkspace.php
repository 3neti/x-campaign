<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignExecutionHandoffWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionHandoffs;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class RepositoryBackedCampaignExecutionHandoffWorkspace implements CampaignExecutionHandoffWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly PlansCampaignExecutionHandoffs $planner,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function plan(
        string $planningKey,
        string $executionId,
        string $requestedBy = 'system',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignExecutionHandoffResultData {
        $plan = $this->requirePlan($planningKey);
        $executionPlan = $this->requireExecutionPlan($plan, $executionId);

        return $this->planner->plan(new CampaignExecutionHandoffData(
            planningKey: $planningKey,
            executionPlan: $executionPlan,
            requestedBy: $requestedBy,
            correlationId: $correlationId,
            metadata: [
                ...$metadata,
                'source' => $metadata['source'] ?? 'repository-backed-execution-handoff-workspace',
                'workspace' => 'repository-backed',
            ],
        ));
    }

    /**
     * @return array<string, bool>
     */
    public function effects(): array
    {
        return [
            ...$this->repository->effects(),
            'integrates_repository' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }

    private function requirePlan(string $planningKey): CampaignPlanData
    {
        $plan = $this->repository->get($planningKey);

        if (! $plan instanceof CampaignPlanData) {
            throw new InvalidArgumentException("Unknown campaign planning key [{$planningKey}].");
        }

        return $plan;
    }

    private function requireExecutionPlan(CampaignPlanData $plan, string $executionId): CampaignExecutionPlanData
    {
        foreach ($plan->executions as $executionPlan) {
            if ($executionPlan instanceof CampaignExecutionPlanData && $executionPlan->execution->id === $executionId) {
                return $executionPlan;
            }
        }

        throw new InvalidArgumentException("Unknown campaign execution plan [{$executionId}].");
    }
}
