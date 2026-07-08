<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignQueueDispatches;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchData;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchResultData;

it('defines a serializable queue dispatch intent without dispatching jobs', function () {
    $dispatch = new CampaignQueueDispatchData(
        planningKey: 'plan-001',
        job: 'campaign.execution.plan',
        payload: ['audience_key' => 'aud-001'],
        metadata: ['correlation_id' => 'corr-001'],
    );

    expect($dispatch->planningKey)->toBe('plan-001')
        ->and($dispatch->queue)->toBe('campaigns')
        ->and($dispatch->connection)->toBeNull()
        ->and($dispatch->delaySeconds)->toBe(0)
        ->and($dispatch->payload)->toBe(['audience_key' => 'aud-001'])
        ->and($dispatch->metadata)->toBe(['correlation_id' => 'corr-001'])
        ->and($dispatch->effects)->toBeInstanceOf(CampaignPersistenceEffectData::class)
        ->and($dispatch->effects->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($dispatch->toArray())->toMatchArray([
            'planning_key' => 'plan-001',
            'queue' => 'campaigns',
            'connection' => null,
            'job' => 'campaign.execution.plan',
            'payload' => ['audience_key' => 'aud-001'],
            'delay_seconds' => 0,
            'metadata' => ['correlation_id' => 'corr-001'],
        ]);
});

it('defines a queue dispatch result envelope that remains planning-only by default', function () {
    $dispatch = new CampaignQueueDispatchData(
        planningKey: 'plan-001',
        job: 'campaign.execution.plan',
        payload: ['audience_key' => 'aud-001'],
    );

    $result = new CampaignQueueDispatchResultData(
        status: 'planned',
        dispatchId: 'dispatch-001',
        dispatch: $dispatch,
        metadata: ['source' => 'phase-3b'],
    );

    expect($result->status)->toBe('planned')
        ->and($result->dispatchId)->toBe('dispatch-001')
        ->and($result->dispatch)->toBe($dispatch)
        ->and($result->metadata)->toBe(['source' => 'phase-3b'])
        ->and($result->effects->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a queue dispatch planning contract before real queue dispatch exists', function () {
    expect(interface_exists(PlansCampaignQueueDispatches::class))->toBeTrue()
        ->and(method_exists(PlansCampaignQueueDispatches::class, 'plan'))->toBeTrue();
});
