<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsSnapshots;
use LBHurtado\XCampaign\Contracts\BuildsCampaignClaimVisibilitySummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignDeliveryHandoffSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignPortableCodeGenerationSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Contracts\CampaignAnalyticsWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPortableCodeGenerationWorkspace;
use LBHurtado\XCampaign\Data\CampaignAnalyticsInputData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class RepositoryBackedCampaignAnalyticsWorkspace implements CampaignAnalyticsWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly BuildsCampaignSummaries $campaignSummaries,
        private readonly CampaignPortableCodeGenerationWorkspace $generationWorkspace,
        private readonly BuildsCampaignPortableCodeGenerationSummaries $generationSummaries,
        private readonly CampaignDeliveryHandoffWorkspace $deliveryWorkspace,
        private readonly BuildsCampaignDeliveryHandoffSummaries $deliverySummaries,
        private readonly CampaignClaimVisibilityWorkspace $claimVisibilityWorkspace,
        private readonly BuildsCampaignClaimVisibilitySummaries $claimVisibilitySummaries,
        private readonly BuildsCampaignAnalyticsSnapshots $snapshots,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function snapshot(
        string $planningKey,
        string $executionId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignAnalyticsSnapshotData {
        $plan = $this->requirePlan($planningKey);
        $executionPlan = $this->requireExecutionPlan($plan, $executionId);
        $workspaceMetadata = [
            ...$metadata,
            'workspace' => 'repository-backed',
            'analytics_only' => true,
        ];

        $generationResult = $this->generationWorkspace->plan($planningKey, $executionId, correlationId: $correlationId, metadata: $workspaceMetadata);
        $deliveryResult = $this->deliveryWorkspace->plan($planningKey, $executionId, $channel, correlationId: $correlationId, metadata: $workspaceMetadata);
        $claimVisibilityResult = $this->claimVisibilityWorkspace->plan($planningKey, $executionId, correlationId: $correlationId, metadata: $workspaceMetadata);

        return $this->snapshots->build(new CampaignAnalyticsInputData(
            planningKey: $planningKey,
            executionId: $executionId,
            campaignSummary: $this->campaignSummaries->fromPlan($plan),
            generationSummary: $this->generationSummaries->fromWorkspaceResult($generationResult),
            deliverySummary: $this->deliverySummaries->fromWorkspaceResult($deliveryResult),
            claimVisibilitySummary: $this->claimVisibilitySummaries->fromWorkspaceResult($claimVisibilityResult),
            metadata: [
                ...$workspaceMetadata,
                'campaign_id' => $executionPlan->execution->campaignId,
                'audience_id' => $executionPlan->execution->audienceId,
                'execution_id' => $executionId,
                'channel' => $channel,
            ],
            effects: new CampaignPersistenceEffectData,
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
