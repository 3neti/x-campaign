<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignQueueDispatch;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchData;

it('plans a queue dispatch intent without pushing work to a queue', function () {
    $planner = new PlanCampaignQueueDispatch;

    $dispatch = new CampaignQueueDispatchData(
        planningKey: 'plan-001',
        job: 'campaign.execution.plan',
        payload: ['audience_key' => 'aud-001'],
        metadata: ['correlation_id' => 'corr-001'],
    );

    $result = $planner->plan($dispatch);

    expect($result->status)->toBe('planned')
        ->and($result->dispatchId)->toStartWith('queue-plan-')
        ->and($result->dispatch)->toBe($dispatch)
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'plan-001',
            'job' => 'campaign.execution.plan',
            'queue' => 'campaigns',
            'planned_only' => true,
            'correlation_id' => 'corr-001',
        ])
        ->and($result->effects->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('builds stable dispatch identifiers for the same queue dispatch intent', function () {
    $planner = new PlanCampaignQueueDispatch;

    $dispatch = new CampaignQueueDispatchData(
        planningKey: 'plan-001',
        job: 'campaign.execution.plan',
        payload: ['audience_key' => 'aud-001'],
    );

    expect($planner->plan($dispatch)->dispatchId)
        ->toBe($planner->plan($dispatch)->dispatchId);
});

it('fails closed before planning incomplete queue dispatch intents', function (CampaignQueueDispatchData $dispatch) {
    (new PlanCampaignQueueDispatch)->plan($dispatch);
})->throws(InvalidArgumentException::class)->with([
    'empty planning key' => fn () => new CampaignQueueDispatchData(
        planningKey: '',
        job: 'campaign.execution.plan',
    ),
    'empty job' => fn () => new CampaignQueueDispatchData(
        planningKey: 'plan-001',
        job: '',
    ),
    'empty queue' => fn () => new CampaignQueueDispatchData(
        planningKey: 'plan-001',
        job: 'campaign.execution.plan',
        queue: '',
    ),
]);
