<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperationalHealthSnapshots;
use LBHurtado\XCampaign\Contracts\CampaignCockpitWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignOperationalMonitorWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\PresentsCampaignCockpitApiResponses;
use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalSignalData;

class RepositoryBackedCampaignOperationalMonitorWorkspace implements CampaignOperationalMonitorWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly CampaignCockpitWorkspace $cockpitWorkspace,
        private readonly PresentsCampaignCockpitApiResponses $apiResponses,
        private readonly BuildsCampaignOperationalHealthSnapshots $healthSnapshots,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function snapshot(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignOperationalHealthSnapshotData {
        $workspaceMetadata = [
            ...$metadata,
            'workspace' => 'repository-backed',
            'operational_monitor_only' => true,
        ];

        $cockpitSummary = $this->cockpitWorkspace->summary(
            planningKey: $planningKey,
            executionId: $executionId,
            operatorId: $operatorId,
            channel: $channel,
            correlationId: $correlationId,
            metadata: $workspaceMetadata,
        );

        return $this->healthSnapshots->build(new CampaignOperationalSignalData(
            planningKey: $planningKey,
            executionId: $executionId,
            operatorId: $operatorId,
            cockpitSummary: $cockpitSummary,
            apiResponse: $this->apiResponses->present($cockpitSummary),
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
