<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use LBHurtado\XCampaign\Contracts\BuildsCampaignProductionReadinessAssessments;
use LBHurtado\XCampaign\Contracts\CampaignOperationalMonitorWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignProductionReadinessWorkspace;
use LBHurtado\XCampaign\Contracts\PresentsCampaignOperationalReadiness;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessChecklistData;

class RepositoryBackedCampaignProductionReadinessWorkspace implements CampaignProductionReadinessWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly CampaignOperationalMonitorWorkspace $monitorWorkspace,
        private readonly PresentsCampaignOperationalReadiness $readinessPresenter,
        private readonly BuildsCampaignProductionReadinessAssessments $assessments,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function assess(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignProductionReadinessAssessmentData {
        $workspaceMetadata = [
            ...$metadata,
            'workspace' => 'repository-backed',
            'production_readiness_only' => true,
        ];

        $snapshot = $this->monitorWorkspace->snapshot(
            planningKey: $planningKey,
            executionId: $executionId,
            operatorId: $operatorId,
            channel: $channel,
            correlationId: $correlationId,
            metadata: $workspaceMetadata,
        );

        return $this->assessments->build(new CampaignProductionReadinessChecklistData(
            planningKey: $planningKey,
            executionId: $executionId,
            operatorId: $operatorId,
            operationalReadiness: $this->readinessPresenter->present($snapshot),
            metadata: $workspaceMetadata,
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
}
