<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiDescriptors;
use LBHurtado\XCampaign\Contracts\CampaignPublicApiWorkspace;
use LBHurtado\XCampaign\Contracts\PresentsCampaignPublicApiResponses;
use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiRequestData;
use LBHurtado\XCampaign\Data\CampaignPublicApiResponseData;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiDescriptorBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiResponsePresenter;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPublicApiWorkspace;

it('has the complete Phase 14 public api baseline surface', function () {
    expect(interface_exists(BuildsCampaignPublicApiDescriptors::class))->toBeTrue()
        ->and(interface_exists(CampaignPublicApiWorkspace::class))->toBeTrue()
        ->and(interface_exists(PresentsCampaignPublicApiResponses::class))->toBeTrue()
        ->and(class_exists(CampaignPublicApiRequestData::class))->toBeTrue()
        ->and(class_exists(CampaignPublicApiDescriptorData::class))->toBeTrue()
        ->and(class_exists(CampaignPublicApiResponseData::class))->toBeTrue()
        ->and(class_exists(CampaignPublicApiDescriptorBuilder::class))->toBeTrue()
        ->and(class_exists(RepositoryBackedCampaignPublicApiWorkspace::class))->toBeTrue()
        ->and(class_exists(CampaignPublicApiResponsePresenter::class))->toBeTrue();
});

it('documents every Phase 14 public api boundary', function () {
    $architecture = file_get_contents(__DIR__.'/../../../docs/x-campaign-architecture.md');

    expect($architecture)->toContain('## Phase 14A Boundary')
        ->and($architecture)->toContain('## Phase 14B Boundary')
        ->and($architecture)->toContain('## Phase 14C Boundary')
        ->and($architecture)->toContain('## Phase 14D Boundary')
        ->and($architecture)->toContain('## Phase 14E Boundary')
        ->and($architecture)->toContain('## Phase 14F Boundary');
});

it('does not own host public api transport infrastructure', function () {
    $packageRoot = dirname(__DIR__, 3);

    expect(is_dir($packageRoot.'/src/Http'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Routes'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Controllers'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Requests'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Resources'))->toBeFalse()
        ->and(is_dir($packageRoot.'/routes'))->toBeFalse();
});
