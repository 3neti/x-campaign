<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitConsumptionMaps;
use LBHurtado\XCampaign\Contracts\BuildsCampaignHostMutationAuthorizationChecklists;
use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiEndpointRecommendationMatrices;
use LBHurtado\XCampaign\Contracts\BuildsCampaignXChangeIntegrationManifests;
use LBHurtado\XCampaign\Data\CampaignCockpitConsumptionMapData;
use LBHurtado\XCampaign\Data\CampaignHostMutationAuthorizationChecklistData;
use LBHurtado\XCampaign\Data\CampaignPublicApiEndpointRecommendationMatrixData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitConsumptionMapBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignHostMutationAuthorizationChecklistBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiEndpointRecommendationMatrixBuilder;

it('documents the host adoption parity report', function () {
    $path = dirname(__DIR__, 3).'/docs/PARITY_REPORT.md';

    expect(file_exists($path))->toBeTrue();

    $report = file_get_contents($path);

    expect($report)->toContain('# x-campaign Parity Report')
        ->and($report)->toContain('## Functional Specification vs As-Built Classes')
        ->and($report)->toContain('## Host Adoption Surface')
        ->and($report)->toContain('## Remaining Gaps')
        ->and($report)->toContain('Campaign lifecycle')
        ->and($report)->toContain('Pay Code generation')
        ->and($report)->toContain('Cockpit consumption')
        ->and($report)->toContain('Host mutation authorization');
});

it('has the complete Phase 15 host adoption baseline surface', function () {
    expect(interface_exists(BuildsCampaignXChangeIntegrationManifests::class))->toBeTrue()
        ->and(interface_exists(BuildsCampaignCockpitConsumptionMaps::class))->toBeTrue()
        ->and(interface_exists(BuildsCampaignPublicApiEndpointRecommendationMatrices::class))->toBeTrue()
        ->and(interface_exists(BuildsCampaignHostMutationAuthorizationChecklists::class))->toBeTrue()
        ->and(class_exists(CampaignXChangeIntegrationRequestData::class))->toBeTrue()
        ->and(class_exists(CampaignXChangeIntegrationManifestData::class))->toBeTrue()
        ->and(class_exists(CampaignCockpitConsumptionMapData::class))->toBeTrue()
        ->and(class_exists(CampaignPublicApiEndpointRecommendationMatrixData::class))->toBeTrue()
        ->and(class_exists(CampaignHostMutationAuthorizationChecklistData::class))->toBeTrue()
        ->and(class_exists(CampaignCockpitConsumptionMapBuilder::class))->toBeTrue()
        ->and(class_exists(CampaignPublicApiEndpointRecommendationMatrixBuilder::class))->toBeTrue()
        ->and(class_exists(CampaignHostMutationAuthorizationChecklistBuilder::class))->toBeTrue();
});

it('documents every Phase 15 host adoption boundary', function () {
    $architecture = file_get_contents(dirname(__DIR__, 3).'/docs/x-campaign-architecture.md');

    expect($architecture)->toContain('## Phase 15A Boundary')
        ->and($architecture)->toContain('## Phase 15B Boundary')
        ->and($architecture)->toContain('## Phase 15C Boundary')
        ->and($architecture)->toContain('## Phase 15D Boundary')
        ->and($architecture)->toContain('## Phase 15E Boundary')
        ->and($architecture)->toContain('## Phase 15F Boundary');
});
