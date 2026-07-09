<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostIntegrationManifests;
use LBHurtado\XCampaign\Contracts\CampaignHostIntegrationWorkspace;
use LBHurtado\XCampaign\Contracts\PresentsCampaignHostIntegrationResponses;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationRequestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationResponseData;
use LBHurtado\XCampaign\ReadModels\CampaignHostIntegrationManifestBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignHostIntegrationResponsePresenter;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignHostIntegrationWorkspace;

it('keeps the phase 13 host integration implementation in parity with the scaffold plan', function () {
    expect(interface_exists(BuildsCampaignHostIntegrationManifests::class))->toBeTrue()
        ->and(interface_exists(CampaignHostIntegrationWorkspace::class))->toBeTrue()
        ->and(interface_exists(PresentsCampaignHostIntegrationResponses::class))->toBeTrue()
        ->and(class_exists(CampaignHostIntegrationRequestData::class))->toBeTrue()
        ->and(class_exists(CampaignHostIntegrationManifestData::class))->toBeTrue()
        ->and(class_exists(CampaignHostIntegrationResponseData::class))->toBeTrue()
        ->and(class_exists(CampaignHostIntegrationManifestBuilder::class))->toBeTrue()
        ->and(class_exists(RepositoryBackedCampaignHostIntegrationWorkspace::class))->toBeTrue()
        ->and(class_exists(CampaignHostIntegrationResponsePresenter::class))->toBeTrue();
});

it('documents every phase 13 architecture boundary and avoids host infrastructure ownership', function () {
    $root = dirname(__DIR__, 3);
    $architecture = file_get_contents($root.'/docs/x-campaign-architecture.md');

    expect($architecture)->toContain('## Phase 13A Boundary')
        ->and($architecture)->toContain('## Phase 13B Boundary')
        ->and($architecture)->toContain('## Phase 13C Boundary')
        ->and($architecture)->toContain('## Phase 13D Boundary')
        ->and($architecture)->toContain('## Phase 13E Boundary')
        ->and($architecture)->toContain('## Phase 13F Boundary')
        ->and(is_dir($root.'/src/Http'))->toBeFalse()
        ->and(is_dir($root.'/src/Routes'))->toBeFalse()
        ->and(is_dir($root.'/src/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Middleware'))->toBeFalse()
        ->and(is_dir($root.'/src/Policies'))->toBeFalse()
        ->and(is_dir($root.'/routes'))->toBeFalse();
});
