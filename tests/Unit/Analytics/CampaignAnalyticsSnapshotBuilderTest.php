<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsSnapshots;
use LBHurtado\XCampaign\Data\CampaignAnalyticsInputData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilitySummaryData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffSummaryData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationSummaryData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;
use LBHurtado\XCampaign\ReadModels\CampaignAnalyticsSnapshotBuilder;

it('builds a ready read-only analytics snapshot from existing summaries', function () {
    $snapshot = (new CampaignAnalyticsSnapshotBuilder)->build(phase8cAnalyticsInput());

    expect(new CampaignAnalyticsSnapshotBuilder)->toBeInstanceOf(BuildsCampaignAnalyticsSnapshots::class)
        ->and($snapshot)->toBeInstanceOf(CampaignAnalyticsSnapshotData::class)
        ->and($snapshot->status)->toBe('ready')
        ->and($snapshot->planningKey)->toBe('planning-analytics')
        ->and($snapshot->executionId)->toBe('execution-analytics')
        ->and($snapshot->recipientCount)->toBe(3)
        ->and($snapshot->generatedCount)->toBe(3)
        ->and($snapshot->deliveryReadyCount)->toBe(2)
        ->and($snapshot->claimVisibleCount)->toBe(2)
        ->and($snapshot->claimedCount)->toBe(1)
        ->and($snapshot->blockers)->toBe([])
        ->and($snapshot->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($snapshot->metadata)->toMatchArray([
            'read_only' => true,
            'source' => 'analytics-snapshot-builder',
        ]);
});

it('marks snapshots attention required when upstream summaries expose blockers', function () {
    $snapshot = (new CampaignAnalyticsSnapshotBuilder)->build(phase8cAnalyticsInput(
        deliveryBlockers: ['one delivery blocked'],
        claimBlockers: ['one claim hidden'],
    ));

    expect($snapshot->status)->toBe('attention_required')
        ->and($snapshot->blockers)->toBe([
            'one delivery blocked',
            'one claim hidden',
        ])
        ->and($snapshot->deliveryReadyCount)->toBe(2)
        ->and($snapshot->claimVisibleCount)->toBe(2);
});

function phase8cAnalyticsInput(array $deliveryBlockers = [], array $claimBlockers = []): CampaignAnalyticsInputData
{
    return new CampaignAnalyticsInputData(
        planningKey: 'planning-analytics',
        executionId: 'execution-analytics',
        campaignSummary: new CampaignSummaryData(recipientCount: 3, executionCount: 1),
        generationSummary: new CampaignPortableCodeGenerationSummaryData('ready', 'planning-analytics', 'execution-analytics', 3, 0, 3, true),
        deliverySummary: new CampaignDeliveryHandoffSummaryData(
            status: $deliveryBlockers === [] ? 'ready' : 'blocked',
            planningKey: 'planning-analytics',
            executionId: 'execution-analytics',
            channel: 'sms',
            recipientCount: 3,
            readyCount: 2,
            blockedCount: count($deliveryBlockers),
            ready: $deliveryBlockers === [],
            blockers: $deliveryBlockers,
        ),
        claimVisibilitySummary: new CampaignClaimVisibilitySummaryData(
            status: $claimBlockers === [] ? 'visible' : 'blocked',
            planningKey: 'planning-analytics',
            executionId: 'execution-analytics',
            recipientCount: 3,
            visibleCount: 2,
            blockedCount: count($claimBlockers),
            claimedCount: 1,
            unclaimedCount: 1,
            visible: $claimBlockers === [],
            blockers: $claimBlockers,
        ),
        metadata: ['operator_scope' => 'summary'],
    );
}
