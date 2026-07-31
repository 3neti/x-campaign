<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPortableCodeGenerationWorkspace;
use LBHurtado\XCampaign\Contracts\PlansCampaignPortableCodeGenerations;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

class RepositoryBackedCampaignPortableCodeGenerationWorkspace implements CampaignPortableCodeGenerationWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly PlansCampaignPortableCodeGenerations $planner,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function plan(
        string $planningKey,
        string $executionId,
        ?string $batchId = null,
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignPortableCodeGenerationWorkspaceResultData {
        $plan = $this->requirePlan($planningKey);
        $executionPlan = $this->requireExecutionPlan($plan, $executionId);
        $audience = $this->requireAudiencePlan($plan, $executionPlan);

        if ($audience->recipients === []) {
            return $this->result(
                status: 'blocked',
                planningKey: $planningKey,
                executionPlan: $executionPlan,
                generationResults: [],
                blockers: ['Portable code generation requires at least one planned recipient.'],
                recipientCount: 0,
                metadata: $metadata,
            );
        }

        $generationResults = array_values(array_map(
            fn (CampaignRecipientData $recipient): CampaignPortableCodeGenerationResultData => $this->planner->plan(
                new CampaignPortableCodeGenerationRequestData(
                    planningKey: $planningKey,
                    execution: $executionPlan->execution,
                    recipient: $recipient,
                    batchId: $batchId,
                    correlationId: $correlationId ?? $executionPlan->execution->correlationId,
                    metadata: [
                        ...$metadata,
                        'source' => $metadata['source'] ?? 'repository-backed-portable-code-generation-workspace',
                        'workspace' => 'repository-backed',
                    ],
                ),
            ),
            $audience->recipients,
        ));

        return $this->result(
            status: $this->status($generationResults),
            planningKey: $planningKey,
            executionPlan: $executionPlan,
            generationResults: $generationResults,
            blockers: $this->blockers($generationResults),
            recipientCount: count($audience->recipients),
            metadata: $metadata,
        );
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

    private function requireAudiencePlan(CampaignPlanData $plan, CampaignExecutionPlanData $executionPlan): CampaignAudiencePlanData
    {
        foreach ($plan->audiences as $audience) {
            if ($audience instanceof CampaignAudiencePlanData && $audience->audience->id === $executionPlan->execution->audienceId) {
                return $audience;
            }
        }

        throw new InvalidArgumentException("Unknown campaign audience plan [{$executionPlan->execution->audienceId}].");
    }

    /**
     * @param  array<int, CampaignPortableCodeGenerationResultData>  $generationResults
     * @param  array<int, string>  $blockers
     * @param  array<string, mixed>  $metadata
     */
    private function result(
        string $status,
        string $planningKey,
        CampaignExecutionPlanData $executionPlan,
        array $generationResults,
        array $blockers,
        int $recipientCount,
        array $metadata,
    ): CampaignPortableCodeGenerationWorkspaceResultData {
        return new CampaignPortableCodeGenerationWorkspaceResultData(
            status: $status,
            planningKey: $planningKey,
            executionId: $executionPlan->execution->id,
            generationResults: $generationResults,
            blockers: $blockers,
            metadata: [
                ...$metadata,
                'planning_key' => $planningKey,
                'campaign_id' => $executionPlan->execution->campaignId,
                'audience_id' => $executionPlan->execution->audienceId,
                'execution_id' => $executionPlan->execution->id,
                'recipient_count' => $recipientCount,
                'generation_count' => count($generationResults),
                'gateway_invoked' => false,
                'workspace' => 'repository-backed',
            ],
            effects: new CampaignPersistenceEffectData,
        );
    }

    /**
     * @param  array<int, CampaignPortableCodeGenerationResultData>  $generationResults
     */
    private function status(array $generationResults): string
    {
        foreach ($generationResults as $generationResult) {
            if ($generationResult->status !== 'planned') {
                return 'blocked';
            }
        }

        return 'planned';
    }

    /**
     * @param  array<int, CampaignPortableCodeGenerationResultData>  $generationResults
     * @return array<int, string>
     */
    private function blockers(array $generationResults): array
    {
        return array_values(array_unique(array_merge(...array_map(
            fn (CampaignPortableCodeGenerationResultData $generationResult): array => $generationResult->blockers,
            $generationResults,
        ))));
    }
}
