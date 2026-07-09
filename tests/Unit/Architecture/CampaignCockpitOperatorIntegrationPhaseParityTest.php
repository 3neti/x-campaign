<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitSummaries;
use LBHurtado\XCampaign\Contracts\CampaignCockpitWorkspace;
use LBHurtado\XCampaign\Contracts\PresentsCampaignCockpitApiResponses;
use LBHurtado\XCampaign\Data\CampaignCockpitApiResponseData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitApiResponsePresenter;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitSummaryBuilder;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignCockpitWorkspace;

it('keeps the phase 10 cockpit and operator integration implementation in parity with the plan', function () {
    expect(interface_exists(BuildsCampaignCockpitSummaries::class))->toBeTrue()
        ->and(interface_exists(CampaignCockpitWorkspace::class))->toBeTrue()
        ->and(interface_exists(PresentsCampaignCockpitApiResponses::class))->toBeTrue()
        ->and(class_exists(CampaignCockpitSummaryRequestData::class))->toBeTrue()
        ->and(class_exists(CampaignCockpitSummaryData::class))->toBeTrue()
        ->and(class_exists(CampaignCockpitSummaryBuilder::class))->toBeTrue()
        ->and(class_exists(RepositoryBackedCampaignCockpitWorkspace::class))->toBeTrue()
        ->and(class_exists(CampaignCockpitApiResponseData::class))->toBeTrue()
        ->and(class_exists(CampaignCockpitApiResponsePresenter::class))->toBeTrue();
});

it('documents all phase 10 slices and does not introduce concrete cockpit http or ui infrastructure', function () {
    $root = dirname(__DIR__, 3);
    $architecture = file_get_contents($root.'/docs/x-campaign-architecture.md');

    expect($architecture)->toContain('## Phase 10A Boundary')
        ->and($architecture)->toContain('## Phase 10B Boundary')
        ->and($architecture)->toContain('## Phase 10C Boundary')
        ->and($architecture)->toContain('## Phase 10D Boundary')
        ->and($architecture)->toContain('## Phase 10E Boundary')
        ->and($architecture)->toContain('## Phase 10F Boundary')
        ->and(is_dir($root.'/src/Http'))->toBeFalse()
        ->and(is_dir($root.'/src/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Routes'))->toBeFalse()
        ->and(is_dir($root.'/resources/js'))->toBeFalse()
        ->and(is_dir($root.'/resources/views'))->toBeFalse();
});
