<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperationalHealthSnapshots;
use LBHurtado\XCampaign\Data\CampaignCockpitApiResponseData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\Data\CampaignOperationalSignalData;
use LBHurtado\XCampaign\ReadModels\CampaignOperationalHealthSnapshotBuilder;

it('builds healthy snapshots from ready cockpit and api signals', function () {
    $builder = new CampaignOperationalHealthSnapshotBuilder;

    $snapshot = $builder->build(phase11cOperationalSignal());

    expect($builder)->toBeInstanceOf(BuildsCampaignOperationalHealthSnapshots::class)
        ->and($snapshot->status)->toBe('healthy')
        ->and($snapshot->checks)->toMatchArray([
            'cockpit' => 'ready',
            'api_response' => 'ok',
        ])
        ->and($snapshot->indicators)->toMatchArray([
            'blocker_count' => 0,
            'action_count' => 1,
        ])
        ->and($snapshot->metadata)->toMatchArray([
            'source' => 'campaign-operational-health-snapshot-builder',
            'read_only' => true,
            'exports_metrics' => false,
            'sends_alerts' => false,
        ]);
});

it('builds attention snapshots when cockpit or api state carries blockers', function () {
    $builder = new CampaignOperationalHealthSnapshotBuilder;

    $snapshot = $builder->build(phase11cOperationalSignal(
        cockpitSummary: new CampaignCockpitSummaryData(
            status: 'attention_required',
            planningKey: 'planning-health',
            executionId: 'execution-health',
            operatorId: 'operator-health',
            blockers: ['cockpit blocker'],
        ),
        apiResponse: new CampaignCockpitApiResponseData(
            status: 'attention_required',
            data: ['blockers' => ['api blocker']],
        ),
    ));

    expect($snapshot->status)->toBe('attention_required')
        ->and($snapshot->blockers)->toBe(['cockpit blocker', 'api blocker'])
        ->and($snapshot->indicators)->toMatchArray(['blocker_count' => 2]);
});

function phase11cOperationalSignal(
    ?CampaignCockpitSummaryData $cockpitSummary = null,
    ?CampaignCockpitApiResponseData $apiResponse = null,
): CampaignOperationalSignalData {
    return new CampaignOperationalSignalData(
        planningKey: 'planning-health',
        executionId: 'execution-health',
        operatorId: 'operator-health',
        cockpitSummary: $cockpitSummary ?? new CampaignCockpitSummaryData(
            status: 'ready',
            planningKey: 'planning-health',
            executionId: 'execution-health',
            operatorId: 'operator-health',
            actions: ['refresh' => ['available' => true]],
        ),
        apiResponse: $apiResponse ?? new CampaignCockpitApiResponseData(status: 'ok'),
    );
}
