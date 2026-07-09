<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperationalHealthSnapshots;
use LBHurtado\XCampaign\Contracts\CampaignOperationalMonitorWorkspace;
use LBHurtado\XCampaign\Contracts\PresentsCampaignOperationalReadiness;
use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalReadinessData;
use LBHurtado\XCampaign\Data\CampaignOperationalSignalData;
use LBHurtado\XCampaign\ReadModels\CampaignOperationalHealthSnapshotBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignOperationalReadinessPresenter;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignOperationalMonitorWorkspace;

it('keeps the phase 11 observability implementation in parity with the plan', function () {
    expect(interface_exists(BuildsCampaignOperationalHealthSnapshots::class))->toBeTrue()
        ->and(interface_exists(CampaignOperationalMonitorWorkspace::class))->toBeTrue()
        ->and(interface_exists(PresentsCampaignOperationalReadiness::class))->toBeTrue()
        ->and(class_exists(CampaignOperationalSignalData::class))->toBeTrue()
        ->and(class_exists(CampaignOperationalHealthSnapshotData::class))->toBeTrue()
        ->and(class_exists(CampaignOperationalReadinessData::class))->toBeTrue()
        ->and(class_exists(CampaignOperationalHealthSnapshotBuilder::class))->toBeTrue()
        ->and(class_exists(RepositoryBackedCampaignOperationalMonitorWorkspace::class))->toBeTrue()
        ->and(class_exists(CampaignOperationalReadinessPresenter::class))->toBeTrue();
});

it('documents all phase 11 slices and does not introduce concrete observability transports', function () {
    $root = dirname(__DIR__, 3);
    $architecture = file_get_contents($root.'/docs/x-campaign-architecture.md');

    expect($architecture)->toContain('## Phase 11A Boundary')
        ->and($architecture)->toContain('## Phase 11B Boundary')
        ->and($architecture)->toContain('## Phase 11C Boundary')
        ->and($architecture)->toContain('## Phase 11D Boundary')
        ->and($architecture)->toContain('## Phase 11E Boundary')
        ->and($architecture)->toContain('## Phase 11F Boundary')
        ->and(is_dir($root.'/src/Metrics'))->toBeFalse()
        ->and(is_dir($root.'/src/Alerts'))->toBeFalse()
        ->and(is_dir($root.'/src/Loggers'))->toBeFalse()
        ->and(is_dir($root.'/src/Monitoring'))->toBeFalse()
        ->and(is_dir($root.'/src/Listeners'))->toBeFalse();
});
