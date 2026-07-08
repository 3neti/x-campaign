<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsSnapshots;
use LBHurtado\XCampaign\Data\CampaignAnalyticsInputData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilitySummaryData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffSummaryData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationSummaryData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;

it('defines an analytics input envelope over existing read-only summaries', function () {
    $input = phase8bAnalyticsInput();

    expect($input->planningKey)->toBe('planning-analytics')
        ->and($input->executionId)->toBe('execution-analytics')
        ->and($input->campaignSummary)->toBeInstanceOf(CampaignSummaryData::class)
        ->and($input->generationSummary)->toBeInstanceOf(CampaignPortableCodeGenerationSummaryData::class)
        ->and($input->deliverySummary)->toBeInstanceOf(CampaignDeliveryHandoffSummaryData::class)
        ->and($input->claimVisibilitySummary)->toBeInstanceOf(CampaignClaimVisibilitySummaryData::class)
        ->and($input->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an analytics snapshot result with no side effects', function () {
    $snapshot = new CampaignAnalyticsSnapshotData(
        status: 'ready',
        planningKey: 'planning-analytics',
        executionId: 'execution-analytics',
        recipientCount: 3,
        generatedCount: 3,
        deliveryReadyCount: 2,
        claimVisibleCount: 2,
        claimedCount: 1,
        blockers: ['one delivery blocked'],
        metadata: ['read_only' => true],
    );

    expect($snapshot->status)->toBe('ready')
        ->and($snapshot->recipientCount)->toBe(3)
        ->and($snapshot->generatedCount)->toBe(3)
        ->and($snapshot->deliveryReadyCount)->toBe(2)
        ->and($snapshot->claimVisibleCount)->toBe(2)
        ->and($snapshot->claimedCount)->toBe(1)
        ->and($snapshot->blockers)->toBe(['one delivery blocked'])
        ->and($snapshot->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an analytics snapshot aggregation contract', function () {
    expect(interface_exists(BuildsCampaignAnalyticsSnapshots::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignAnalyticsSnapshots::class, 'build'))->toBeTrue();
});

function phase8bAnalyticsInput(): CampaignAnalyticsInputData
{
    return new CampaignAnalyticsInputData(
        planningKey: 'planning-analytics',
        executionId: 'execution-analytics',
        campaignSummary: new CampaignSummaryData(recipientCount: 3, executionCount: 1),
        generationSummary: new CampaignPortableCodeGenerationSummaryData('ready', 'planning-analytics', 'execution-analytics', 3, 0, 3, true),
        deliverySummary: new CampaignDeliveryHandoffSummaryData('ready', 'planning-analytics', 'execution-analytics', 'sms', 3, 2, 1, false, ['one delivery blocked']),
        claimVisibilitySummary: new CampaignClaimVisibilitySummaryData('visible', 'planning-analytics', 'execution-analytics', 3, 2, 1, 1, 1, false),
    );
}

