<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignExecutionHandoffSummaries;
use LBHurtado\XCampaign\Data\CampaignBatchData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffSummaryData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\ReadModels\CampaignExecutionHandoffSummaryReadModel;

it('builds a read-only execution handoff summary from a handoff result', function () {
    $result = new CampaignExecutionHandoffResultData(
        status: 'ready',
        handoffId: 'handoff-001',
        handoff: new CampaignExecutionHandoffData(
            planningKey: 'planning-001',
            executionPlan: new CampaignExecutionPlanData(
                execution: new CampaignExecutionData(
                    id: 'execution-001',
                    campaignId: 'campaign-001',
                    audienceId: 'audience-001',
                    status: 'planned',
                ),
                batches: [
                    new CampaignBatchData(id: 'batch-001', executionId: 'execution-001', sequence: 1, recipientCount: 4),
                    new CampaignBatchData(id: 'batch-002', executionId: 'execution-001', sequence: 2, recipientCount: 6),
                ],
            ),
        ),
        metadata: ['handoff_only' => true],
    );

    $summary = (new CampaignExecutionHandoffSummaryReadModel)->fromResult($result);

    expect(new CampaignExecutionHandoffSummaryReadModel)->toBeInstanceOf(BuildsCampaignExecutionHandoffSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignExecutionHandoffSummaryData::class)
        ->and($summary->status)->toBe('ready')
        ->and($summary->handoffId)->toBe('handoff-001')
        ->and($summary->planningKey)->toBe('planning-001')
        ->and($summary->executionId)->toBe('execution-001')
        ->and($summary->batchCount)->toBe(2)
        ->and($summary->recipientCount)->toBe(10)
        ->and($summary->ready)->toBeTrue()
        ->and($summary->blockers)->toBe([])
        ->and($summary->effects)->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('summarizes blocked execution handoff results without hiding blockers', function () {
    $result = new CampaignExecutionHandoffResultData(
        status: 'blocked',
        handoffId: 'handoff-002',
        handoff: new CampaignExecutionHandoffData(
            planningKey: 'planning-002',
            executionPlan: new CampaignExecutionPlanData(
                execution: new CampaignExecutionData(
                    id: 'execution-002',
                    campaignId: 'campaign-001',
                    audienceId: 'audience-001',
                    status: 'planned',
                ),
            ),
        ),
        blockers: ['Execution handoff requires at least one planned batch.'],
    );

    $summary = (new CampaignExecutionHandoffSummaryReadModel)->fromResult($result);

    expect($summary->ready)->toBeFalse()
        ->and($summary->blockers)->toBe(['Execution handoff requires at least one planned batch.'])
        ->and($summary->batchCount)->toBe(0)
        ->and($summary->recipientCount)->toBe(0);
});
