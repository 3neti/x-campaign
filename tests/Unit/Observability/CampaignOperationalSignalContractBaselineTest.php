<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperationalHealthSnapshots;
use LBHurtado\XCampaign\Data\CampaignCockpitApiResponseData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalSignalData;

it('defines an operational signal over cockpit summary and api response state', function () {
    $signal = new CampaignOperationalSignalData(
        planningKey: 'planning-observe',
        executionId: 'execution-observe',
        operatorId: 'operator-observe',
        cockpitSummary: new CampaignCockpitSummaryData(
            status: 'ready',
            planningKey: 'planning-observe',
            executionId: 'execution-observe',
            operatorId: 'operator-observe',
        ),
        apiResponse: new CampaignCockpitApiResponseData(status: 'ok'),
        metadata: ['source' => 'test'],
    );

    expect($signal->planningKey)->toBe('planning-observe')
        ->and($signal->cockpitSummary)->toBeInstanceOf(CampaignCockpitSummaryData::class)
        ->and($signal->apiResponse)->toBeInstanceOf(CampaignCockpitApiResponseData::class)
        ->and($signal->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an operational health snapshot without exporter or alert effects', function () {
    $snapshot = new CampaignOperationalHealthSnapshotData(
        status: 'healthy',
        planningKey: 'planning-observe',
        executionId: 'execution-observe',
        operatorId: 'operator-observe',
        checks: ['cockpit' => 'ready'],
        indicators: ['blocker_count' => 0],
        blockers: [],
        metadata: ['health_snapshot' => true],
    );

    expect($snapshot->status)->toBe('healthy')
        ->and($snapshot->checks)->toMatchArray(['cockpit' => 'ready'])
        ->and($snapshot->indicators)->toMatchArray(['blocker_count' => 0])
        ->and($snapshot->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an operational health snapshot builder contract', function () {
    expect(interface_exists(BuildsCampaignOperationalHealthSnapshots::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignOperationalHealthSnapshots::class, 'build'))->toBeTrue();
});
