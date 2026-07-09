<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiDescriptors;
use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiRequestData;

class CampaignPublicApiDescriptorBuilder implements BuildsCampaignPublicApiDescriptors
{
    public function build(CampaignPublicApiRequestData $request): CampaignPublicApiDescriptorData
    {
        return new CampaignPublicApiDescriptorData(
            status: 'described',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            operatorId: $request->operatorId,
            apiVersion: $request->apiVersion,
            endpoints: [
                'campaign.summary' => [
                    'method' => 'GET',
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                    'read_only' => true,
                ],
                'campaign.cockpit' => [
                    'method' => 'GET',
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                    'read_only' => true,
                ],
                'campaign.production_readiness' => [
                    'method' => 'GET',
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                    'read_only' => true,
                ],
                'campaign.host_integration' => [
                    'method' => 'GET',
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                    'read_only' => true,
                ],
            ],
            hostResponsibilities: [
                'routes',
                'controllers',
                'request_validation',
                'api_resources',
                'middleware',
                'policies',
                'authentication',
                'authorization',
                'redaction',
                'rate_limiting',
            ],
            packageResponsibilities: [
                'descriptors',
                'read_models',
                'workspace_contracts',
                'response_presenters',
                'effect_metadata',
            ],
            metadata: [
                ...$request->metadata,
                'source' => 'campaign-public-api-descriptor-builder',
                'read_only' => true,
                'registers_routes' => false,
                'registers_controllers' => false,
                'owns_requests' => false,
                'owns_resources' => false,
            ],
            effects: $request->effects,
        );
    }
}
