<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignProductionReadinessAssessments;
use LBHurtado\XCampaign\Contracts\CampaignProductionReadinessWorkspace;
use LBHurtado\XCampaign\Contracts\PresentsCampaignProductionReleases;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessChecklistData;
use LBHurtado\XCampaign\Data\CampaignProductionReleaseData;
use LBHurtado\XCampaign\ReadModels\CampaignProductionReadinessAssessmentBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignProductionReleasePresenter;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignProductionReadinessWorkspace;

it('keeps the phase 12 production readiness implementation in parity with the scaffold plan', function () {
    expect(interface_exists(BuildsCampaignProductionReadinessAssessments::class))->toBeTrue()
        ->and(interface_exists(CampaignProductionReadinessWorkspace::class))->toBeTrue()
        ->and(interface_exists(PresentsCampaignProductionReleases::class))->toBeTrue()
        ->and(class_exists(CampaignProductionReadinessChecklistData::class))->toBeTrue()
        ->and(class_exists(CampaignProductionReadinessAssessmentData::class))->toBeTrue()
        ->and(class_exists(CampaignProductionReleaseData::class))->toBeTrue()
        ->and(class_exists(CampaignProductionReadinessAssessmentBuilder::class))->toBeTrue()
        ->and(class_exists(RepositoryBackedCampaignProductionReadinessWorkspace::class))->toBeTrue()
        ->and(class_exists(CampaignProductionReleasePresenter::class))->toBeTrue();
});

it('documents every phase 12 architecture boundary and avoids release infrastructure ownership', function () {
    $root = dirname(__DIR__, 3);
    $architecture = file_get_contents($root.'/docs/x-campaign-architecture.md');

    expect($architecture)->toContain('## Phase 12A Boundary')
        ->and($architecture)->toContain('## Phase 12B Boundary')
        ->and($architecture)->toContain('## Phase 12C Boundary')
        ->and($architecture)->toContain('## Phase 12D Boundary')
        ->and($architecture)->toContain('## Phase 12E Boundary')
        ->and($architecture)->toContain('## Phase 12F Boundary')
        ->and(is_dir($root.'/src/Deployments'))->toBeFalse()
        ->and(is_dir($root.'/src/Releases'))->toBeFalse()
        ->and(is_dir($root.'/src/EnvironmentWriters'))->toBeFalse()
        ->and(is_dir($root.'/src/Installers'))->toBeFalse()
        ->and(is_dir($root.'/src/Provisioning'))->toBeFalse()
        ->and(is_dir($root.'/src/Workers'))->toBeFalse();
});
