<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignExportHandoffs;
use LBHurtado\XCampaign\Data\CampaignExportHandoffRequestData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\ReadModels\CampaignExportHandoffPlanner;

it('plans an export handoff without generating storing or delivering files', function () {
    $planner = new CampaignExportHandoffPlanner;

    $result = $planner->plan(phase9fExportHandoffRequest());

    expect($planner)->toBeInstanceOf(PlansCampaignExportHandoffs::class)
        ->and($result->status)->toBe('planned')
        ->and($result->planningKey)->toBe('planning-export')
        ->and($result->executionId)->toBe('execution-export')
        ->and($result->exportId)->toBe('export-planning-export-execution-export-csv')
        ->and($result->format)->toBe('csv')
        ->and($result->destination)->toBe('operator_download')
        ->and($result->blockers)->toBe([])
        ->and($result->manifest)->toMatchArray([
            'report_type' => 'operator_summary',
            'format' => 'csv',
            'destination' => 'operator_download',
            'sections' => ['overview', 'audience', 'execution'],
            'export_generated' => false,
            'file_stored' => false,
            'delivery_sent' => false,
        ])
        ->and($result->metadata)->toMatchArray([
            'operator_id' => 'operator-export',
            'source' => 'campaign-export-handoff-planner',
            'handoff_only' => true,
            'read_only' => true,
        ])
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

it('blocks export handoff planning when the operator report is not ready', function () {
    $planner = new CampaignExportHandoffPlanner;

    $request = phase9fExportHandoffRequest(report: new CampaignOperatorReportData(
        status: 'blocked',
        planningKey: 'planning-export',
        executionId: 'execution-export',
        reportType: 'operator_summary',
        format: 'array',
        title: 'Campaign Operator Summary',
        sections: ['overview' => ['recipient_count' => 10]],
        blockers: ['report has unresolved funding blockers'],
    ));

    $result = $planner->plan($request);

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toBe(['report has unresolved funding blockers'])
        ->and($result->manifest)->toMatchArray([
            'export_generated' => false,
            'file_stored' => false,
            'delivery_sent' => false,
        ]);
});

function phase9fExportHandoffRequest(?CampaignOperatorReportData $report = null): CampaignExportHandoffRequestData
{
    return new CampaignExportHandoffRequestData(
        planningKey: 'planning-export',
        executionId: 'execution-export',
        format: 'csv',
        destination: 'operator_download',
        report: $report ?? new CampaignOperatorReportData(
            status: 'ready',
            planningKey: 'planning-export',
            executionId: 'execution-export',
            reportType: 'operator_summary',
            format: 'array',
            title: 'Campaign Operator Summary',
            sections: [
                'overview' => ['recipient_count' => 10],
                'audience' => ['approved_count' => 9],
                'execution' => ['queued_count' => 9],
            ],
        ),
        metadata: ['operator_id' => 'operator-export'],
    );
}
