<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

it('defines a serializable queued plan payload with correlation metadata', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'plan-001',
        operation: 'execution.plan',
        payload: ['audience_key' => 'aud-001'],
        metadata: ['operator_id' => 'operator-001'],
        correlationId: 'corr-001',
    );

    expect($payload->planningKey)->toBe('plan-001')
        ->and($payload->operation)->toBe('execution.plan')
        ->and($payload->payload)->toBe(['audience_key' => 'aud-001'])
        ->and($payload->metadata)->toBe(['operator_id' => 'operator-001'])
        ->and($payload->correlationId)->toBe('corr-001')
        ->and($payload->toArray())->toBe([
            'planning_key' => 'plan-001',
            'operation' => 'execution.plan',
            'payload' => ['audience_key' => 'aud-001'],
            'metadata' => ['operator_id' => 'operator-001'],
            'correlation_id' => 'corr-001',
        ]);
});

it('generates a stable correlation identifier when one is not supplied', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'plan-001',
        operation: 'execution.plan',
        payload: ['audience_key' => 'aud-001'],
    );

    expect($payload->correlationId)
        ->toStartWith('campaign-queue-')
        ->toBe((new CampaignQueuedPlanPayloadData(
            planningKey: 'plan-001',
            operation: 'execution.plan',
            payload: ['audience_key' => 'aud-001'],
        ))->correlationId);
});

it('fails closed before creating incomplete queued plan payloads', function (array $arguments) {
    new CampaignQueuedPlanPayloadData(...$arguments);
})->throws(InvalidArgumentException::class)->with([
    'empty planning key' => [[
        'planningKey' => '',
        'operation' => 'execution.plan',
    ]],
    'empty operation' => [[
        'planningKey' => 'plan-001',
        'operation' => '',
    ]],
]);
