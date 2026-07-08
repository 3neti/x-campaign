<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitSummaries;
use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryRequestData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitSummaryBuilder;

it('builds an operator-safe cockpit summary from existing read models', function () {
    $builder = new CampaignCockpitSummaryBuilder;

    $summary = $builder->build(phase10cCockpitRequest());

    expect($builder)->toBeInstanceOf(BuildsCampaignCockpitSummaries::class)
        ->and($summary->status)->toBe('ready')
        ->and($summary->cards)->toMatchArray([
            'campaign' => [
                'campaign_id' => 'campaign-cockpit',
                'name' => 'Cockpit Campaign',
                'status' => 'ready',
                'recipient_count' => 10,
            ],
            'analytics' => [
                'operator_posture' => 'ready',
                'generated_count' => 10,
                'delivery_ready_count' => 9,
                'claim_visible_count' => 8,
                'claimed_count' => 5,
            ],
            'export' => [
                'status' => 'planned',
                'format' => 'csv',
                'destination' => 'operator_download',
            ],
        ])
        ->and($summary->panels)->toMatchArray([
            'report' => [
                'title' => 'Campaign Operator Summary',
                'sections' => ['overview' => ['recipient_count' => 10]],
            ],
        ])
        ->and($summary->actions)->toMatchArray([
            'refresh' => ['available' => true, 'method' => 'read'],
            'export' => ['available' => true, 'method' => 'handoff'],
        ])
        ->and($summary->metadata)->toMatchArray([
            'operator_id' => 'operator-cockpit',
            'source' => 'campaign-cockpit-summary-builder',
            'read_only' => true,
        ])
        ->and($summary->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('surfaces blockers from analytics report and export handoff without hiding operator risk', function () {
    $builder = new CampaignCockpitSummaryBuilder;

    $request = phase10cCockpitRequest(
        analyticsSummary: new CampaignAnalyticsOperatorSummaryData(
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            status: 'attention_required',
            operatorPosture: 'attention_required',
            blockerCount: 1,
            blockers: ['analytics blocker'],
        ),
        operatorReport: new CampaignOperatorReportData(
            status: 'attention_required',
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            reportType: 'operator_summary',
            format: 'array',
            title: 'Campaign Operator Summary',
            blockers: ['report blocker'],
        ),
        exportHandoff: new CampaignExportHandoffResultData(
            status: 'blocked',
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            exportId: 'export-planning-cockpit-execution-cockpit-csv',
            format: 'csv',
            destination: 'operator_download',
            blockers: ['export blocker'],
        ),
    );

    $summary = $builder->build($request);

    expect($summary->status)->toBe('attention_required')
        ->and($summary->blockers)->toBe(['analytics blocker', 'report blocker', 'export blocker'])
        ->and($summary->actions)->toMatchArray([
            'export' => ['available' => false, 'method' => 'handoff'],
        ]);
});

function phase10cCockpitRequest(
    ?CampaignAnalyticsOperatorSummaryData $analyticsSummary = null,
    ?CampaignOperatorReportData $operatorReport = null,
    ?CampaignExportHandoffResultData $exportHandoff = null,
): CampaignCockpitSummaryRequestData {
    return new CampaignCockpitSummaryRequestData(
        planningKey: 'planning-cockpit',
        executionId: 'execution-cockpit',
        operatorId: 'operator-cockpit',
        campaignSummary: new CampaignSummaryData(
            campaignId: 'campaign-cockpit',
            name: 'Cockpit Campaign',
            status: 'ready',
            recipientCount: 10,
        ),
        analyticsSummary: $analyticsSummary ?? new CampaignAnalyticsOperatorSummaryData(
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            status: 'ready',
            operatorPosture: 'ready',
            recipientCount: 10,
            generatedCount: 10,
            deliveryReadyCount: 9,
            claimVisibleCount: 8,
            claimedCount: 5,
            ready: true,
        ),
        operatorReport: $operatorReport ?? new CampaignOperatorReportData(
            status: 'ready',
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            reportType: 'operator_summary',
            format: 'array',
            title: 'Campaign Operator Summary',
            sections: ['overview' => ['recipient_count' => 10]],
        ),
        exportHandoff: $exportHandoff ?? new CampaignExportHandoffResultData(
            status: 'planned',
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            exportId: 'export-planning-cockpit-execution-cockpit-csv',
            format: 'csv',
            destination: 'operator_download',
        ),
        metadata: ['operator_context' => 'cockpit'],
    );
}
