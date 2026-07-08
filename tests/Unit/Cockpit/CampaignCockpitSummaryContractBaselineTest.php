<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitSummaries;
use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryRequestData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;

it('defines a cockpit summary request over existing campaign operator read models', function () {
    $request = phase10bCockpitRequest();

    expect($request->planningKey)->toBe('planning-cockpit')
        ->and($request->executionId)->toBe('execution-cockpit')
        ->and($request->operatorId)->toBe('operator-cockpit')
        ->and($request->campaignSummary)->toBeInstanceOf(CampaignSummaryData::class)
        ->and($request->analyticsSummary)->toBeInstanceOf(CampaignAnalyticsOperatorSummaryData::class)
        ->and($request->operatorReport)->toBeInstanceOf(CampaignOperatorReportData::class)
        ->and($request->exportHandoff)->toBeInstanceOf(CampaignExportHandoffResultData::class)
        ->and($request->metadata)->toMatchArray(['source' => 'test'])
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

it('defines a cockpit summary result without api routes or mutation effects', function () {
    $summary = new CampaignCockpitSummaryData(
        status: 'ready',
        planningKey: 'planning-cockpit',
        executionId: 'execution-cockpit',
        operatorId: 'operator-cockpit',
        cards: ['overview' => ['recipient_count' => 10]],
        panels: ['analytics' => ['ready' => true]],
        actions: ['refresh' => ['available' => true]],
        blockers: [],
        metadata: ['cockpit_summary' => true],
    );

    expect($summary->status)->toBe('ready')
        ->and($summary->cards)->toMatchArray(['overview' => ['recipient_count' => 10]])
        ->and($summary->panels)->toMatchArray(['analytics' => ['ready' => true]])
        ->and($summary->actions)->toMatchArray(['refresh' => ['available' => true]])
        ->and($summary->metadata)->toMatchArray(['cockpit_summary' => true])
        ->and($summary->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a cockpit summary builder contract', function () {
    expect(interface_exists(BuildsCampaignCockpitSummaries::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignCockpitSummaries::class, 'build'))->toBeTrue();
});

function phase10bCockpitRequest(): CampaignCockpitSummaryRequestData
{
    return new CampaignCockpitSummaryRequestData(
        planningKey: 'planning-cockpit',
        executionId: 'execution-cockpit',
        operatorId: 'operator-cockpit',
        campaignSummary: new CampaignSummaryData(
            status: 'ready',
            campaignId: 'campaign-cockpit',
            name: 'Cockpit Campaign',
            audienceCount: 1,
            recipientCount: 10,
        ),
        analyticsSummary: new CampaignAnalyticsOperatorSummaryData(
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
        operatorReport: new CampaignOperatorReportData(
            status: 'ready',
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            reportType: 'operator_summary',
            format: 'array',
            title: 'Campaign Operator Summary',
            sections: ['overview' => ['recipient_count' => 10]],
        ),
        exportHandoff: new CampaignExportHandoffResultData(
            status: 'planned',
            planningKey: 'planning-cockpit',
            executionId: 'execution-cockpit',
            exportId: 'export-planning-cockpit-execution-cockpit-csv',
            format: 'csv',
            destination: 'operator_download',
        ),
        metadata: ['source' => 'test'],
    );
}
