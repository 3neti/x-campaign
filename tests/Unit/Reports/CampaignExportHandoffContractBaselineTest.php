<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignExportHandoffs;
use LBHurtado\XCampaign\Data\CampaignExportHandoffRequestData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;

it('defines an export handoff request over an operator report without generating files', function () {
    $request = phase9dExportHandoffRequest();

    expect($request->planningKey)->toBe('planning-export')
        ->and($request->executionId)->toBe('execution-export')
        ->and($request->format)->toBe('csv')
        ->and($request->destination)->toBe('operator_download')
        ->and($request->report)->toBeInstanceOf(CampaignOperatorReportData::class)
        ->and($request->metadata)->toMatchArray(['operator_id' => 'operator-export'])
        ->and($request->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an export handoff result without file storage or delivery effects', function () {
    $result = new CampaignExportHandoffResultData(
        status: 'planned',
        planningKey: 'planning-export',
        executionId: 'execution-export',
        exportId: 'export-planning-export-execution-export-csv',
        format: 'csv',
        destination: 'operator_download',
        manifest: ['columns' => ['recipient_count', 'claimed_count']],
        blockers: [],
        metadata: ['export_generated' => false],
    );

    expect($result->status)->toBe('planned')
        ->and($result->exportId)->toBe('export-planning-export-execution-export-csv')
        ->and($result->manifest)->toMatchArray(['columns' => ['recipient_count', 'claimed_count']])
        ->and($result->metadata)->toMatchArray(['export_generated' => false])
        ->and($result->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an export handoff planning contract', function () {
    expect(interface_exists(PlansCampaignExportHandoffs::class))->toBeTrue()
        ->and(method_exists(PlansCampaignExportHandoffs::class, 'plan'))->toBeTrue();
});

function phase9dExportHandoffRequest(): CampaignExportHandoffRequestData
{
    return new CampaignExportHandoffRequestData(
        planningKey: 'planning-export',
        executionId: 'execution-export',
        format: 'csv',
        destination: 'operator_download',
        report: new CampaignOperatorReportData(
            status: 'ready',
            planningKey: 'planning-export',
            executionId: 'execution-export',
            reportType: 'operator_summary',
            format: 'array',
            title: 'Campaign Operator Summary',
            sections: ['overview' => ['recipient_count' => 10]],
        ),
        metadata: ['operator_id' => 'operator-export'],
    );
}
