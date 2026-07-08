<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperatorReports;
use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignOperatorReportBuilder;

it('builds a read-only operator report from an analytics summary', function () {
    $report = (new CampaignOperatorReportBuilder)->build(phase9cOperatorReportRequest());

    expect(new CampaignOperatorReportBuilder)->toBeInstanceOf(BuildsCampaignOperatorReports::class)
        ->and($report)->toBeInstanceOf(CampaignOperatorReportData::class)
        ->and($report->status)->toBe('ready')
        ->and($report->planningKey)->toBe('planning-report')
        ->and($report->executionId)->toBe('execution-report')
        ->and($report->reportType)->toBe('operator_summary')
        ->and($report->format)->toBe('array')
        ->and($report->title)->toBe('Campaign Operator Summary')
        ->and($report->sections)->toMatchArray([
            'overview' => [
                'recipient_count' => 10,
                'generated_count' => 10,
                'delivery_ready_count' => 9,
                'claim_visible_count' => 8,
                'claimed_count' => 5,
            ],
            'operator' => [
                'posture' => 'ready_for_review',
                'ready' => true,
                'blocker_count' => 0,
            ],
        ])
        ->and($report->blockers)->toBe([])
        ->and($report->metadata)->toMatchArray([
            'source' => 'campaign-operator-report-builder',
            'read_only' => true,
            'operator_panel' => 'campaign-report',
        ]);
});

it('preserves blockers and attention required report status', function () {
    $request = phase9cOperatorReportRequest(status: 'attention_required', blockers: ['delivery backlog']);

    $report = (new CampaignOperatorReportBuilder)->build($request);

    expect($report->status)->toBe('attention_required')
        ->and($report->blockers)->toBe(['delivery backlog'])
        ->and($report->sections['operator'])->toMatchArray([
            'posture' => 'attention_required',
            'ready' => false,
            'blocker_count' => 1,
        ]);
});

function phase9cOperatorReportRequest(string $status = 'ready', array $blockers = []): CampaignOperatorReportRequestData
{
    return new CampaignOperatorReportRequestData(
        planningKey: 'planning-report',
        executionId: 'execution-report',
        reportType: 'operator_summary',
        format: 'array',
        analyticsSummary: new CampaignAnalyticsOperatorSummaryData(
            planningKey: 'planning-report',
            executionId: 'execution-report',
            status: $status,
            operatorPosture: $status === 'ready' ? 'ready_for_review' : 'attention_required',
            recipientCount: 10,
            generatedCount: 10,
            deliveryReadyCount: 9,
            claimVisibleCount: 8,
            claimedCount: 5,
            blockerCount: count($blockers),
            ready: $status === 'ready',
            blockers: $blockers,
        ),
        metadata: ['operator_panel' => 'campaign-report'],
    );
}
