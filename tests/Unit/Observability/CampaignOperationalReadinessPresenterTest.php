<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignOperationalReadiness;
use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalReadinessData;
use LBHurtado\XCampaign\ReadModels\CampaignOperationalReadinessPresenter;

it('presents healthy snapshots as host-safe readiness envelopes without exporting metrics', function () {
    $presenter = new CampaignOperationalReadinessPresenter;

    $readiness = $presenter->present(phase11eHealthSnapshot());

    expect($presenter)->toBeInstanceOf(PresentsCampaignOperationalReadiness::class)
        ->and($readiness)->toBeInstanceOf(CampaignOperationalReadinessData::class)
        ->and($readiness->status)->toBe('ready')
        ->and($readiness->summary)->toMatchArray([
            'planning_key' => 'planning-readiness',
            'execution_id' => 'execution-readiness',
            'operator_id' => 'operator-readiness',
            'health_status' => 'healthy',
        ])
        ->and($readiness->meta)->toMatchArray([
            'source' => 'campaign-operational-readiness-presenter',
            'read_only' => true,
            'exports_metrics' => false,
            'sends_alerts' => false,
            'writes_journal' => false,
        ]);
});

it('presents attention snapshots without hiding blockers', function () {
    $presenter = new CampaignOperationalReadinessPresenter;

    $readiness = $presenter->present(new CampaignOperationalHealthSnapshotData(
        status: 'attention_required',
        planningKey: 'planning-readiness',
        executionId: 'execution-readiness',
        operatorId: 'operator-readiness',
        blockers: ['operational blocker'],
    ));

    expect($readiness->status)->toBe('attention_required')
        ->and($readiness->summary)->toMatchArray([
            'blockers' => ['operational blocker'],
        ]);
});

function phase11eHealthSnapshot(): CampaignOperationalHealthSnapshotData
{
    return new CampaignOperationalHealthSnapshotData(
        status: 'healthy',
        planningKey: 'planning-readiness',
        executionId: 'execution-readiness',
        operatorId: 'operator-readiness',
        checks: ['cockpit' => 'ready', 'api_response' => 'ok'],
        indicators: ['blocker_count' => 0],
    );
}
