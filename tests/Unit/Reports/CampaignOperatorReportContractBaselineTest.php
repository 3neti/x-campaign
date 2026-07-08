<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperatorReports;
use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportRequestData;

it('defines an operator report request over existing analytics operator summaries', function () {
    $request = phase9bOperatorReportRequest();

    expect($request->planningKey)->toBe('planning-report')
        ->and($request->executionId)->toBe('execution-report')
        ->and($request->reportType)->toBe('operator_summary')
        ->and($request->format)->toBe('array')
        ->and($request->analyticsSummary)->toBeInstanceOf(CampaignAnalyticsOperatorSummaryData::class)
        ->and($request->metadata)->toMatchArray(['operator_panel' => 'campaign-report'])
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

it('defines an operator report result without rendering, storage, or delivery effects', function () {
    $report = new CampaignOperatorReportData(
        status: 'ready',
        planningKey: 'planning-report',
        executionId: 'execution-report',
        reportType: 'operator_summary',
        format: 'array',
        title: 'Campaign Operator Summary',
        sections: ['overview' => ['recipient_count' => 10]],
        blockers: [],
        metadata: ['read_only' => true],
    );

    expect($report->status)->toBe('ready')
        ->and($report->title)->toBe('Campaign Operator Summary')
        ->and($report->sections)->toMatchArray(['overview' => ['recipient_count' => 10]])
        ->and($report->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an operator report builder contract', function () {
    expect(interface_exists(BuildsCampaignOperatorReports::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignOperatorReports::class, 'build'))->toBeTrue();
});

function phase9bOperatorReportRequest(): CampaignOperatorReportRequestData
{
    return new CampaignOperatorReportRequestData(
        planningKey: 'planning-report',
        executionId: 'execution-report',
        reportType: 'operator_summary',
        format: 'array',
        analyticsSummary: new CampaignAnalyticsOperatorSummaryData(
            planningKey: 'planning-report',
            executionId: 'execution-report',
            status: 'ready',
            operatorPosture: 'ready_for_review',
            recipientCount: 10,
            generatedCount: 10,
            deliveryReadyCount: 9,
            claimVisibleCount: 8,
            claimedCount: 5,
            ready: true,
        ),
        metadata: ['operator_panel' => 'campaign-report'],
    );
}
