<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiEndpointRecommendationMatrices;
use LBHurtado\XCampaign\Data\CampaignPublicApiEndpointRecommendationMatrixData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

class CampaignPublicApiEndpointRecommendationMatrixBuilder implements BuildsCampaignPublicApiEndpointRecommendationMatrices
{
    public function build(CampaignXChangeIntegrationRequestData $request): CampaignPublicApiEndpointRecommendationMatrixData
    {
        return new CampaignPublicApiEndpointRecommendationMatrixData(
            status: 'recommended',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            operatorId: $request->operatorId,
            endpointRecommendations: [
                'campaign.summary' => $this->get('x-change.campaign.summary', 'CampaignPlanningWorkspace', 'CampaignCockpitApiResponsePresenter'),
                'campaign.cockpit' => $this->get('x-change.campaign.cockpit', 'CampaignCockpitWorkspace', 'CampaignCockpitApiResponsePresenter'),
                'campaign.production_readiness' => $this->get('x-change.campaign.production-readiness', 'CampaignProductionReadinessWorkspace', 'CampaignProductionReleasePresenter'),
                'campaign.host_integration' => $this->get('x-change.campaign.host-integration', 'CampaignHostIntegrationWorkspace', 'CampaignHostIntegrationResponsePresenter'),
                'campaign.public_api' => $this->get('x-change.campaign.public-api', 'CampaignPublicApiWorkspace', 'CampaignPublicApiResponsePresenter'),
            ],
            hostResponsibilities: [
                'route_names',
                'controller_methods',
                'request_validation',
                'authorization',
                'redaction',
                'rate_limiting',
                'api_resources',
            ],
            packageResponsibilities: [
                'workspace_contracts',
                'response_presenters',
                'endpoint_descriptors',
                'effect_metadata',
            ],
            metadata: [
                ...$request->metadata,
                'source' => 'campaign-public-api-endpoint-recommendation-matrix-builder',
                'registers_routes' => false,
                'registers_controllers' => false,
                'read_only' => true,
            ],
            effects: $request->effects,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function get(string $routeName, string $workspace, string $presenter): array
    {
        return [
            'method' => 'GET',
            'route_name' => $routeName,
            'workspace' => $workspace,
            'presenter' => $presenter,
            'mutation' => false,
            'host_registers_route' => true,
            'package_registers_route' => false,
        ];
    }
}
