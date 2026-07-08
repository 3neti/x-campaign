<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToAnalyticsSnapshots;
use LBHurtado\XCampaign\Data\CampaignAnalyticsWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadAnalyticsSnapshotMapper;

it('maps queued analytics payloads to workspace input without running analytics', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-analytics',
        operation: 'analytics.snapshot',
        payload: [
            'execution_id' => 'execution-analytics',
            'channel' => 'email',
        ],
        metadata: ['operator_id' => 'operator-analytics'],
        correlationId: 'corr-analytics',
    );

    $input = (new CampaignQueuedPayloadAnalyticsSnapshotMapper)->map($payload);

    expect(new CampaignQueuedPayloadAnalyticsSnapshotMapper)->toBeInstanceOf(MapsCampaignQueuedPayloadsToAnalyticsSnapshots::class)
        ->and($input)->toBeInstanceOf(CampaignAnalyticsWorkspaceInputData::class)
        ->and($input->planningKey)->toBe('planning-analytics')
        ->and($input->executionId)->toBe('execution-analytics')
        ->and($input->channel)->toBe('email')
        ->and($input->correlationId)->toBe('corr-analytics')
        ->and($input->metadata)->toMatchArray([
            'operator_id' => 'operator-analytics',
            'operation' => 'analytics.snapshot',
            'source' => 'queued-payload-analytics-snapshot-mapper',
            'analytics_only' => true,
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

it('defaults queued analytics channel to sms when absent', function () {
    $input = (new CampaignQueuedPayloadAnalyticsSnapshotMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-analytics',
        operation: 'analytics.snapshot',
        payload: ['execution_id' => 'execution-analytics'],
    ));

    expect($input->channel)->toBe('sms');
});

it('fails closed for queued payloads that are not analytics snapshot operations', function () {
    expect(fn () => (new CampaignQueuedPayloadAnalyticsSnapshotMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-analytics',
        operation: 'claim.visibility',
        payload: ['execution_id' => 'execution-analytics'],
    )))->toThrow(InvalidArgumentException::class, 'Queued campaign payload operation [claim.visibility] cannot be mapped to analytics snapshots.');
});

it('fails closed before mapping queued analytics payloads without an execution id', function () {
    expect(fn () => (new CampaignQueuedPayloadAnalyticsSnapshotMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-analytics',
        operation: 'analytics.snapshot',
        payload: [],
    )))->toThrow(InvalidArgumentException::class, 'Queued analytics snapshot payloads require an execution_id.');
});
