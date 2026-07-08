<?php

declare(strict_types=1);

use Illuminate\Contracts\Queue\ShouldQueue;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Jobs\ProcessCampaignQueuedPlan;

it('defines a queue job wrapper that carries a queued plan payload', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'plan-001',
        operation: 'execution.plan',
        payload: ['audience_key' => 'aud-001'],
        correlationId: 'corr-001',
    );

    $job = new ProcessCampaignQueuedPlan($payload);

    expect($job)->toBeInstanceOf(ShouldQueue::class)
        ->and($job->queue)->toBe('campaigns')
        ->and($job->payload()->toArray())->toBe($payload->toArray())
        ->and($job->effects()->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('keeps the queue job wrapper serializable for queue transport', function () {
    $job = new ProcessCampaignQueuedPlan(new CampaignQueuedPlanPayloadData(
        planningKey: 'plan-001',
        operation: 'execution.plan',
        payload: ['audience_key' => 'aud-001'],
        correlationId: 'corr-001',
    ));

    $restored = unserialize(serialize($job));

    expect($restored)->toBeInstanceOf(ProcessCampaignQueuedPlan::class)
        ->and($restored->payload()->toArray())->toBe($job->payload()->toArray());
});

it('handles queued plan payloads as a no-op wrapper before execution integration exists', function () {
    $job = new ProcessCampaignQueuedPlan(new CampaignQueuedPlanPayloadData(
        planningKey: 'plan-001',
        operation: 'execution.plan',
        payload: ['audience_key' => 'aud-001'],
    ));

    expect($job->handle())->toBeNull()
        ->and($job->effects()->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});
