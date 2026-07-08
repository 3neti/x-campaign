<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperatorReports;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExportHandoffs;
use LBHurtado\XCampaign\Contracts\PlansCampaignExportHandoffs;
use LBHurtado\XCampaign\Data\CampaignExportHandoffRequestData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignExportHandoffPlanner;
use LBHurtado\XCampaign\ReadModels\CampaignOperatorReportBuilder;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadExportHandoffMapper;

it('keeps the phase 9 operator report and export handoff implementation in parity with the plan', function () {
    expect(interface_exists(BuildsCampaignOperatorReports::class))->toBeTrue()
        ->and(interface_exists(PlansCampaignExportHandoffs::class))->toBeTrue()
        ->and(interface_exists(MapsCampaignQueuedPayloadsToExportHandoffs::class))->toBeTrue()
        ->and(class_exists(CampaignOperatorReportRequestData::class))->toBeTrue()
        ->and(class_exists(CampaignOperatorReportData::class))->toBeTrue()
        ->and(class_exists(CampaignOperatorReportBuilder::class))->toBeTrue()
        ->and(class_exists(CampaignExportHandoffRequestData::class))->toBeTrue()
        ->and(class_exists(CampaignExportHandoffResultData::class))->toBeTrue()
        ->and(class_exists(CampaignExportHandoffWorkspaceInputData::class))->toBeTrue()
        ->and(class_exists(CampaignExportHandoffPlanner::class))->toBeTrue()
        ->and(class_exists(CampaignQueuedPayloadExportHandoffMapper::class))->toBeTrue();
});

it('documents all phase 9 slices and does not introduce concrete report transport infrastructure', function () {
    $root = dirname(__DIR__, 3);
    $architecture = file_get_contents($root.'/docs/x-campaign-architecture.md');

    expect($architecture)->toContain('## Phase 9A Boundary')
        ->and($architecture)->toContain('## Phase 9B Boundary')
        ->and($architecture)->toContain('## Phase 9C Boundary')
        ->and($architecture)->toContain('## Phase 9D Boundary')
        ->and($architecture)->toContain('## Phase 9E Boundary')
        ->and($architecture)->toContain('## Phase 9F Boundary')
        ->and(is_dir($root.'/src/Exports'))->toBeFalse()
        ->and(is_dir($root.'/src/ReportTransports'))->toBeFalse()
        ->and(is_dir($root.'/src/Pdf'))->toBeFalse()
        ->and(is_dir($root.'/src/Spreadsheets'))->toBeFalse();
});
