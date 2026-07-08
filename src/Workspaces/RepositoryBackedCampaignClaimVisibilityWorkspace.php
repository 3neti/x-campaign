<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignClaimStatusProvider;
use LBHurtado\XCampaign\Contracts\CampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPortableCodeGenerationWorkspace;
use LBHurtado\XCampaign\Contracts\PlansCampaignClaimVisibilities;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityResultData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;

class RepositoryBackedCampaignClaimVisibilityWorkspace implements CampaignClaimVisibilityWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly CampaignPortableCodeGenerationWorkspace $generationWorkspace,
        private readonly CampaignClaimStatusProvider $claimStatusProvider,
        private readonly PlansCampaignClaimVisibilities $planner,
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
    ): CampaignClaimVisibilityWorkspaceResultData {
        $plan = $this->requirePlan($planningKey);
        $executionPlan = $this->requireExecutionPlan($plan, $executionId);
        $generationWorkspaceResult = $this->generationWorkspace->plan($planningKey, $executionId, correlationId: $correlationId, metadata: $metadata);

        $visibilityResults = array_values(array_map(
            fn (CampaignPortableCodeGenerationResultData $generationResult): CampaignClaimVisibilityResultData => $this->planner->plan(
                new CampaignClaimVisibilityData(
                    planningKey: $planningKey,
                    execution: $executionPlan->execution,
                    recipient: $generationResult->request->recipient,
                    generationResult: $this->withReference($generationResult),
                    claimStatus: $this->claimStatus($this->withReference($generationResult)),
                    requestedBy: $requestedBy,
                    correlationId: $correlationId ?? $executionPlan->execution->correlationId,
                    metadata: [
                        ...$metadata,
                        'source' => $metadata['source'] ?? 'repository-backed-claim-visibility-workspace',
                        'workspace' => 'repository-backed',
                    ],
                ),
            ),
            $generationWorkspaceResult->generationResults,
        ));

        return new CampaignClaimVisibilityWorkspaceResultData(
            status: $this->status($visibilityResults),
            planningKey: $planningKey,
            executionId: $executionId,
            visibilityResults: $visibilityResults,
            blockers: $this->blockers($visibilityResults),
            metadata: [
                ...$metadata,
                'planning_key' => $planningKey,
                'campaign_id' => $executionPlan->execution->campaignId,
                'audience_id' => $executionPlan->execution->audienceId,
                'execution_id' => $executionId,
                'recipient_count' => count($generationWorkspaceResult->generationResults),
                'visibility_count' => count($visibilityResults),
                'claim_runtime_invoked' => false,
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
     * @return array<string, mixed>
     */
    private function claimStatus(CampaignPortableCodeGenerationResultData $generationResult): array
    {
        return $this->claimStatusProvider->status((string) $generationResult->portableCodeReference);
    }

    /**
     * @param  array<int, CampaignClaimVisibilityResultData>  $visibilityResults
     */
    private function status(array $visibilityResults): string
    {
        foreach ($visibilityResults as $visibilityResult) {
            if ($visibilityResult->status !== 'visible') {
                return 'blocked';
            }
        }

        return 'visible';
    }

    /**
     * @param  array<int, CampaignClaimVisibilityResultData>  $visibilityResults
     * @return array<int, string>
     */
    private function blockers(array $visibilityResults): array
    {
        if ($visibilityResults === []) {
            return [];
        }

        return array_values(array_unique(array_merge(...array_map(
            fn (CampaignClaimVisibilityResultData $visibilityResult): array => $visibilityResult->blockers,
            $visibilityResults,
        ))));
    }
}

