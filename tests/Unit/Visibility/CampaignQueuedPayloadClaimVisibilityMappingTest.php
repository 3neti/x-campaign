<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToClaimVisibilities;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadClaimVisibilityMapper;

it('maps queued claim visibility payloads to workspace input without querying claim state', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-visibility',
        operation: 'claim.visibility',
        payload: ['execution_id' => 'execution-visibility'],
        metadata: ['operator_id' => 'operator-001'],
        correlationId: 'corr-visibility',
    );

    $input = (new CampaignQueuedPayloadClaimVisibilityMapper)->map($payload);

    expect(new CampaignQueuedPayloadClaimVisibilityMapper)->toBeInstanceOf(MapsCampaignQueuedPayloadsToClaimVisibilities::class)
        ->and($input)->toBeInstanceOf(CampaignClaimVisibilityWorkspaceInputData::class)
        ->and($input->planningKey)->toBe('planning-visibility')
        ->and($input->executionId)->toBe('execution-visibility')
        ->and($input->requestedBy)->toBe('operator-001')
        ->and($input->correlationId)->toBe('corr-visibility')
        ->and($input->metadata)->toMatchArray([
            'operation' => 'claim.visibility',
            'source' => 'queued-payload-claim-visibility-mapper',
            'visibility_only' => true,
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

it('defaults queued claim visibility requesters to queue when operator metadata is absent', function () {
    $input = (new CampaignQueuedPayloadClaimVisibilityMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-visibility',
        operation: 'claim.visibility',
        payload: ['execution_id' => 'execution-visibility'],
    ));

    expect($input->requestedBy)->toBe('queue');
});

it('fails closed for queued payloads that are not claim visibility operations', function () {
    expect(fn () => (new CampaignQueuedPayloadClaimVisibilityMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-visibility',
        operation: 'delivery.handoff',
        payload: ['execution_id' => 'execution-visibility'],
    )))->toThrow(InvalidArgumentException::class, 'Queued campaign payload operation [delivery.handoff] cannot be mapped to claim visibility.');
});

it('fails closed before mapping queued claim visibility payloads without an execution id', function () {
    expect(fn () => (new CampaignQueuedPayloadClaimVisibilityMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-visibility',
        operation: 'claim.visibility',
        payload: [],
    )))->toThrow(InvalidArgumentException::class, 'Queued claim visibility payloads require an execution_id.');
});

