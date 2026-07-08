<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsOperatorSummaries;
use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;
use LBHurtado\XCampaign\ReadModels\CampaignAnalyticsOperatorSummaryReadModel;

it('builds an operator-facing analytics summary from a ready snapshot', function () {
    $summary = (new CampaignAnalyticsOperatorSummaryReadModel)->fromSnapshot(new CampaignAnalyticsSnapshotData(
        status: 'ready',
        planningKey: 'planning-analytics',
        executionId: 'execution-analytics',
        recipientCount: 4,
        generatedCount: 4,
        deliveryReadyCount: 3,
        claimVisibleCount: 2,
        claimedCount: 1,
        metadata: ['workspace' => 'repository-backed'],
    ), ['operator_panel' => 'campaign-analytics']);

    expect(new CampaignAnalyticsOperatorSummaryReadModel)->toBeInstanceOf(BuildsCampaignAnalyticsOperatorSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignAnalyticsOperatorSummaryData::class)
        ->and($summary->planningKey)->toBe('planning-analytics')
        ->and($summary->executionId)->toBe('execution-analytics')
        ->and($summary->status)->toBe('ready')
        ->and($summary->operatorPosture)->toBe('ready_for_review')
        ->and($summary->recipientCount)->toBe(4)
        ->and($summary->generatedCount)->toBe(4)
        ->and($summary->deliveryReadyCount)->toBe(3)
        ->and($summary->claimVisibleCount)->toBe(2)
        ->and($summary->claimedCount)->toBe(1)
        ->and($summary->blockerCount)->toBe(0)
        ->and($summary->ready)->toBeTrue()
        ->and($summary->effects)->toMatchArray([
            'analytics_operator_summary' => true,
            'read_only' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($summary->metadata)->toMatchArray([
            'workspace' => 'repository-backed',
            'operator_panel' => 'campaign-analytics',
            'source' => 'campaign-analytics-operator-summary-read-model',
            'read_only' => true,
        ]);
});

it('exposes attention required posture and blockers without mutating analytics', function () {
    $summary = (new CampaignAnalyticsOperatorSummaryReadModel)->fromSnapshot(new CampaignAnalyticsSnapshotData(
        status: 'attention_required',
        planningKey: 'planning-analytics',
        executionId: 'execution-analytics',
        recipientCount: 4,
        generatedCount: 4,
        deliveryReadyCount: 2,
        claimVisibleCount: 1,
        claimedCount: 0,
        blockers: ['delivery backlog', 'claim visibility hidden'],
    ));

    expect($summary->operatorPosture)->toBe('attention_required')
        ->and($summary->ready)->toBeFalse()
        ->and($summary->blockerCount)->toBe(2)
        ->and($summary->blockers)->toBe(['delivery backlog', 'claim visibility hidden']);
});
