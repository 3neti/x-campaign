<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExecutionHandoffs;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadExecutionHandoffMapper;

it('maps queued execution handoff payloads to workspace input without executing work', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-handoff',
        operation: 'execution.handoff',
        payload: ['execution_id' => 'execution-handoff'],
        metadata: ['operator_id' => 'operator-001'],
        correlationId: 'corr-handoff',
    );

    $input = (new CampaignQueuedPayloadExecutionHandoffMapper)->map($payload);

    expect(new CampaignQueuedPayloadExecutionHandoffMapper)->toBeInstanceOf(MapsCampaignQueuedPayloadsToExecutionHandoffs::class)
        ->and($input)->toBeInstanceOf(CampaignExecutionHandoffWorkspaceInputData::class)
        ->and($input->planningKey)->toBe('planning-handoff')
        ->and($input->executionId)->toBe('execution-handoff')
        ->and($input->requestedBy)->toBe('operator-001')
        ->and($input->correlationId)->toBe('corr-handoff')
        ->and($input->metadata)->toMatchArray([
            'operation' => 'execution.handoff',
            'source' => 'queued-payload-execution-handoff-mapper',
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

it('defaults queued handoff requesters to queue when operator metadata is absent', function () {
    $input = (new CampaignQueuedPayloadExecutionHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-handoff',
        operation: 'execution.handoff',
        payload: ['execution_id' => 'execution-handoff'],
    ));

    expect($input->requestedBy)->toBe('queue');
});

it('fails closed for queued payloads that are not execution handoff operations', function () {
    expect(fn () => (new CampaignQueuedPayloadExecutionHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-handoff',
        operation: 'execution.plan',
        payload: ['execution_id' => 'execution-handoff'],
    )))->toThrow(InvalidArgumentException::class, 'Queued campaign payload operation [execution.plan] cannot be mapped to an execution handoff.');
});

it('fails closed before mapping queued handoff payloads without an execution id', function () {
    expect(fn () => (new CampaignQueuedPayloadExecutionHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-handoff',
        operation: 'execution.handoff',
        payload: [],
    )))->toThrow(InvalidArgumentException::class, 'Queued execution handoff payloads require an execution_id.');
});
