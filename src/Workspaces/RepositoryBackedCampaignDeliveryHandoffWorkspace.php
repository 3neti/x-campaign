<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPortableCodeGenerationWorkspace;
use LBHurtado\XCampaign\Contracts\PlansCampaignDeliveryHandoffs;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;

class RepositoryBackedCampaignDeliveryHandoffWorkspace implements CampaignDeliveryHandoffWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly CampaignPortableCodeGenerationWorkspace $generationWorkspace,
        private readonly PlansCampaignDeliveryHandoffs $planner,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function plan(
        string $planningKey,
        string $executionId,
        string $channel = 'sms',
        string $requestedBy = 'system',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignDeliveryHandoffWorkspaceResultData {
        $plan = $this->requirePlan($planningKey);
        $executionPlan = $this->requireExecutionPlan($plan, $executionId);
        $generationWorkspaceResult = $this->generationWorkspace->plan($planningKey, $executionId, correlationId: $correlationId, metadata: $metadata);

        $handoffResults = array_values(array_map(
            fn (CampaignPortableCodeGenerationResultData $generationResult): CampaignDeliveryHandoffResultData => $this->planner->plan(
                new CampaignDeliveryHandoffData(
                    planningKey: $planningKey,
                    execution: $executionPlan->execution,
                    recipient: $generationResult->request->recipient,
                    generationResult: $this->withReference($generationResult),
                    channel: $channel,
                    requestedBy: $requestedBy,
                    correlationId: $correlationId ?? $executionPlan->execution->correlationId,
                    metadata: [
                        ...$metadata,
                        'source' => $metadata['source'] ?? 'repository-backed-delivery-handoff-workspace',
                        'workspace' => 'repository-backed',
                    ],
                ),
            ),
            $generationWorkspaceResult->generationResults,
        ));

        return new CampaignDeliveryHandoffWorkspaceResultData(
            status: $this->status($handoffResults),
            planningKey: $planningKey,
            executionId: $executionId,
            channel: $channel,
            handoffResults: $handoffResults,
            blockers: $this->blockers($handoffResults),
            metadata: [
                ...$metadata,
                'planning_key' => $planningKey,
                'campaign_id' => $executionPlan->execution->campaignId,
                'audience_id' => $executionPlan->execution->audienceId,
                'execution_id' => $executionId,
                'channel' => $channel,
                'recipient_count' => count($generationWorkspaceResult->generationResults),
                'handoff_count' => count($handoffResults),
                'delivery_invoked' => false,
                'workspace' => 'repository-backed',
            ],
            effects: new CampaignPersistenceEffectData,
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

    private function withReference(CampaignPortableCodeGenerationResultData $generationResult): CampaignPortableCodeGenerationResultData
    {
        if ($generationResult->portableCodeReference !== null && trim($generationResult->portableCodeReference) !== '') {
            return $generationResult;
        }

        return new CampaignPortableCodeGenerationResultData(
            status: $generationResult->status,
            generationId: $generationResult->generationId,
            request: $generationResult->request,
            portableCodeReference: $generationResult->generationId,
            blockers: $generationResult->blockers,
            metadata: $generationResult->metadata,
            effects: $generationResult->effects,
        );
    }

    /**
     * @param  array<int, CampaignDeliveryHandoffResultData>  $handoffResults
     */
    private function status(array $handoffResults): string
    {
        foreach ($handoffResults as $handoffResult) {
            if ($handoffResult->status !== 'ready') {
                return 'blocked';
            }
        }

        return 'ready';
    }

    /**
     * @param  array<int, CampaignDeliveryHandoffResultData>  $handoffResults
     * @return array<int, string>
     */
    private function blockers(array $handoffResults): array
    {
        return array_values(array_unique(array_merge(...array_map(
            fn (CampaignDeliveryHandoffResultData $handoffResult): array => $handoffResult->blockers,
            $handoffResults,
        ))));
    }
}
