<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use LBHurtado\XCampaign\Contracts\DispatchesCampaignQueuedPlans;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Jobs\ProcessCampaignQueuedPlan;
use LBHurtado\XCampaign\Queue\CampaignQueueDispatcher;

it('binds campaign queued plan dispatching to the queue dispatcher seam', function () {
    $dispatcher = app(DispatchesCampaignQueuedPlans::class);

    expect($dispatcher)->toBeInstanceOf(CampaignQueueDispatcher::class);
});

it('pushes a queued plan wrapper onto the campaign queue without executing campaign work', function () {
    Queue::fake();

    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'plan-001',
        operation: 'execution.plan',
        payload: ['audience_key' => 'aud-001'],
        correlationId: 'corr-001',
    );

    $result = app(DispatchesCampaignQueuedPlans::class)->dispatch($payload);

    Queue::assertPushedOn('campaigns', ProcessCampaignQueuedPlan::class, function (ProcessCampaignQueuedPlan $job) use ($payload): bool {
        return $job->payload()->toArray() === $payload->toArray();
    });

    expect($result->status)->toBe('queued')
        ->and($result->dispatch->planningKey)->toBe('plan-001')
        ->and($result->dispatch->job)->toBe(ProcessCampaignQueuedPlan::class)
        ->and($result->metadata)->toMatchArray([
            'queued' => true,
            'correlation_id' => 'corr-001',
            'operation' => 'execution.plan',
        ])
        ->and($result->effects->toArray())->toMatchArray([
            'queues_jobs' => true,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('records custom queue and connection metadata while keeping the job payload stable', function () {
    Queue::fake();

    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'plan-002',
        operation: 'audience.import.review',
        payload: ['audience_key' => 'aud-002'],
        correlationId: 'corr-002',
    );

    $result = app(DispatchesCampaignQueuedPlans::class)->dispatch(
        payload: $payload,
        queue: 'campaign-imports',
        connection: 'redis',
    );

    Queue::assertPushedOn('campaign-imports', ProcessCampaignQueuedPlan::class);

    expect($result->dispatch->queue)->toBe('campaign-imports')
        ->and($result->dispatch->connection)->toBe('redis')
        ->and($result->dispatch->payload)->toBe($payload->toArray());
});
