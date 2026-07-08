<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToDeliveryHandoffs;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadDeliveryHandoffMapper;

it('maps queued delivery handoff payloads to workspace input without delivering work', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-delivery',
        operation: 'delivery.handoff',
        payload: [
            'execution_id' => 'execution-delivery',
            'channel' => 'email',
        ],
        metadata: ['operator_id' => 'operator-001'],
        correlationId: 'corr-delivery',
    );

    $input = (new CampaignQueuedPayloadDeliveryHandoffMapper)->map($payload);

    expect(new CampaignQueuedPayloadDeliveryHandoffMapper)->toBeInstanceOf(MapsCampaignQueuedPayloadsToDeliveryHandoffs::class)
        ->and($input)->toBeInstanceOf(CampaignDeliveryHandoffWorkspaceInputData::class)
        ->and($input->planningKey)->toBe('planning-delivery')
        ->and($input->executionId)->toBe('execution-delivery')
        ->and($input->channel)->toBe('email')
        ->and($input->requestedBy)->toBe('operator-001')
        ->and($input->correlationId)->toBe('corr-delivery')
        ->and($input->metadata)->toMatchArray([
            'operation' => 'delivery.handoff',
            'source' => 'queued-payload-delivery-handoff-mapper',
            'handoff_only' => true,
        ])
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

it('defaults queued delivery handoff requesters and channels when optional metadata is absent', function () {
    $input = (new CampaignQueuedPayloadDeliveryHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-delivery',
        operation: 'delivery.handoff',
        payload: ['execution_id' => 'execution-delivery'],
    ));

    expect($input->requestedBy)->toBe('queue')
        ->and($input->channel)->toBe('sms');
});

it('fails closed for queued payloads that are not delivery handoff operations', function () {
    expect(fn () => (new CampaignQueuedPayloadDeliveryHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-delivery',
        operation: 'execution.handoff',
        payload: ['execution_id' => 'execution-delivery'],
    )))->toThrow(InvalidArgumentException::class, 'Queued campaign payload operation [execution.handoff] cannot be mapped to a delivery handoff.');
});

it('fails closed before mapping queued delivery handoff payloads without an execution id', function () {
    expect(fn () => (new CampaignQueuedPayloadDeliveryHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-delivery',
        operation: 'delivery.handoff',
        payload: [],
    )))->toThrow(InvalidArgumentException::class, 'Queued delivery handoff payloads require an execution_id.');
});

