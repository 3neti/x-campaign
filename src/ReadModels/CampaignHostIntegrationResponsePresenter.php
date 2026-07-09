<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\PresentsCampaignHostIntegrationResponses;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationResponseData;

class CampaignHostIntegrationResponsePresenter implements PresentsCampaignHostIntegrationResponses
{
    public function present(CampaignHostIntegrationManifestData $manifest): CampaignHostIntegrationResponseData
    {
        return new CampaignHostIntegrationResponseData(
            status: $manifest->status === 'available' ? 'ok' : $manifest->status,
            data: [
                'planning_key' => $manifest->planningKey,
                'execution_id' => $manifest->executionId,
                'operator_id' => $manifest->operatorId,
                'manifest_status' => $manifest->status,
                'capabilities' => $manifest->capabilities,
                'host_responsibilities' => $manifest->hostResponsibilities,
                'package_responsibilities' => $manifest->packageResponsibilities,
                'warnings' => $manifest->warnings,
            ],
            meta: [
                ...$manifest->metadata,
                'source' => 'campaign-host-integration-response-presenter',
                'read_only' => true,
                'registers_routes' => false,
                'registers_controllers' => false,
                'owns_middleware' => false,
                'owns_policies' => false,
            ],
        );
    }
}
