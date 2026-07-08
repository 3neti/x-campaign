<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExportHandoffs;
use LBHurtado\XCampaign\Data\CampaignExportHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadExportHandoffMapper;

it('maps queued export handoff payloads to workspace input without generating exports', function () {
    $payload = new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-export',
        operation: 'report.export',
        payload: [
            'execution_id' => 'execution-export',
            'report_type' => 'operator_summary',
            'format' => 'csv',
            'destination' => 'operator_download',
        ],
        metadata: ['operator_id' => 'operator-export'],
        correlationId: 'corr-export',
    );

    $input = (new CampaignQueuedPayloadExportHandoffMapper)->map($payload);

    expect(new CampaignQueuedPayloadExportHandoffMapper)->toBeInstanceOf(MapsCampaignQueuedPayloadsToExportHandoffs::class)
        ->and($input)->toBeInstanceOf(CampaignExportHandoffWorkspaceInputData::class)
        ->and($input->planningKey)->toBe('planning-export')
        ->and($input->executionId)->toBe('execution-export')
        ->and($input->reportType)->toBe('operator_summary')
        ->and($input->format)->toBe('csv')
        ->and($input->destination)->toBe('operator_download')
        ->and($input->correlationId)->toBe('corr-export')
        ->and($input->metadata)->toMatchArray([
            'operator_id' => 'operator-export',
            'operation' => 'report.export',
            'source' => 'queued-payload-export-handoff-mapper',
            'export_handoff_only' => true,
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

it('defaults queued export report type format and destination when absent', function () {
    $input = (new CampaignQueuedPayloadExportHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-export',
        operation: 'report.export',
        payload: ['execution_id' => 'execution-export'],
    ));

    expect($input->reportType)->toBe('operator_summary')
        ->and($input->format)->toBe('csv')
        ->and($input->destination)->toBe('operator_download');
});

it('fails closed for queued payloads that are not export handoff operations', function () {
    expect(fn () => (new CampaignQueuedPayloadExportHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-export',
        operation: 'analytics.snapshot',
        payload: ['execution_id' => 'execution-export'],
    )))->toThrow(InvalidArgumentException::class, 'Queued campaign payload operation [analytics.snapshot] cannot be mapped to export handoff.');
});

it('fails closed before mapping queued export payloads without an execution id', function () {
    expect(fn () => (new CampaignQueuedPayloadExportHandoffMapper)->map(new CampaignQueuedPlanPayloadData(
        planningKey: 'planning-export',
        operation: 'report.export',
        payload: [],
    )))->toThrow(InvalidArgumentException::class, 'Queued export handoff payloads require an execution_id.');
});
