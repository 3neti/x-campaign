<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiDescriptors;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPublicApiWorkspace;
use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiRequestData;

class RepositoryBackedCampaignPublicApiWorkspace implements CampaignPublicApiWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly BuildsCampaignPublicApiDescriptors $descriptors,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function descriptor(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $apiVersion = 'v1',
        array $metadata = [],
    ): CampaignPublicApiDescriptorData {
        $workspaceMetadata = [
            ...$metadata,
            'workspace' => 'repository-backed',
            'public_api_only' => true,
            'planning_key_exists' => $this->repository->has($planningKey),
        ];

        return $this->descriptors->build(new CampaignPublicApiRequestData(
            planningKey: $planningKey,
            executionId: $executionId,
            operatorId: $operatorId,
            apiVersion: $apiVersion,
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
