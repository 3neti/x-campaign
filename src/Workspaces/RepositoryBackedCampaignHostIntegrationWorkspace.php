<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostIntegrationManifests;
use LBHurtado\XCampaign\Contracts\CampaignHostIntegrationWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationRequestData;

class RepositoryBackedCampaignHostIntegrationWorkspace implements CampaignHostIntegrationWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly BuildsCampaignHostIntegrationManifests $manifests,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function manifest(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignHostIntegrationManifestData {
        $workspaceMetadata = [
            ...$metadata,
            'workspace' => 'repository-backed',
            'host_integration_only' => true,
            'planning_key_exists' => $this->repository->has($planningKey),
        ];

        return $this->manifests->build(new CampaignHostIntegrationRequestData(
            planningKey: $planningKey,
            executionId: $executionId,
            operatorId: $operatorId,
            channel: $channel,
            correlationId: $correlationId,
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
