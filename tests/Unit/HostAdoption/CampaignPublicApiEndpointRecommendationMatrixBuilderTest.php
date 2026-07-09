<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiEndpointRecommendationMatrices;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiEndpointRecommendationMatrixBuilder;

it('builds public api endpoint recommendations without registering routes', function () {
    $matrix = (new CampaignPublicApiEndpointRecommendationMatrixBuilder)->build(new CampaignXChangeIntegrationRequestData(
        planningKey: 'planning-endpoints',
        executionId: 'execution-endpoints',
        operatorId: 'operator-endpoints',
        metadata: ['request_id' => 'request-endpoints'],
    ));

    expect($matrix->status)->toBe('recommended')
        ->and($matrix->endpointRecommendations)->toHaveKeys([
            'campaign.summary',
            'campaign.cockpit',
            'campaign.production_readiness',
            'campaign.host_integration',
            'campaign.public_api',
        ])
        ->and($matrix->endpointRecommendations['campaign.summary'])->toMatchArray([
            'method' => 'GET',
            'route_name' => 'x-change.campaign.summary',
            'workspace' => 'CampaignPlanningWorkspace',
            'presenter' => 'CampaignCockpitApiResponsePresenter',
            'mutation' => false,
            'host_registers_route' => true,
            'package_registers_route' => false,
        ])
        ->and($matrix->hostResponsibilities)->toContain('route_names')
        ->and($matrix->hostResponsibilities)->toContain('controller_methods')
        ->and($matrix->hostResponsibilities)->toContain('request_validation')
        ->and($matrix->metadata)->toMatchArray([
            'request_id' => 'request-endpoints',
            'source' => 'campaign-public-api-endpoint-recommendation-matrix-builder',
            'registers_routes' => false,
            'registers_controllers' => false,
            'read_only' => true,
        ]);
});

it('implements the endpoint recommendation matrix builder contract', function () {
    expect(new CampaignPublicApiEndpointRecommendationMatrixBuilder)
        ->toBeInstanceOf(BuildsCampaignPublicApiEndpointRecommendationMatrices::class);
});
