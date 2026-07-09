<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostIntegrationManifests;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationRequestData;

class CampaignHostIntegrationManifestBuilder implements BuildsCampaignHostIntegrationManifests
{
    public function build(CampaignHostIntegrationRequestData $request): CampaignHostIntegrationManifestData
    {
        return new CampaignHostIntegrationManifestData(
            status: 'available',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            operatorId: $request->operatorId,
            capabilities: [
                'planning_workspace' => 'read_write_in_memory_boundary',
                'cockpit_summary' => 'read_only',
                'analytics_workspace' => 'read_only',
                'operational_monitor' => 'read_only',
                'production_readiness' => 'read_only',
                'release_presentation' => 'read_only',
            ],
            hostResponsibilities: [
                'routes',
                'controllers',
                'middleware',
                'policies',
                'authentication',
                'authorization',
                'redaction',
                'request_validation',
                'api_versioning',
            ],
            packageResponsibilities: [
                'contracts',
                'data_transfer_objects',
                'read_models',
                'workspaces',
                'presenters',
            ],
            warnings: [
                'host authorization required',
                'host redaction required',
                'host route registration required',
            ],
            metadata: [
                ...$request->metadata,
                'source' => 'campaign-host-integration-manifest-builder',
                'read_only' => true,
                'registers_routes' => false,
                'registers_controllers' => false,
                'owns_middleware' => false,
                'owns_policies' => false,
            ],
        );
    }
}
