<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignExecutionHandoff;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionHandoffs;
use LBHurtado\XCampaign\Data\CampaignBatchData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;

it('plans an execution handoff in memory without executing campaign work', function () {
    $executionPlan = new CampaignExecutionPlanData(
        execution: new CampaignExecutionData(
            id: 'execution-001',
            campaignId: 'campaign-001',
            audienceId: 'audience-001',
            status: 'planned',
            correlationId: 'corr-001',
        ),
        batches: [
            new CampaignBatchData(id: 'batch-001', executionId: 'execution-001', sequence: 1, recipientCount: 7),
            new CampaignBatchData(id: 'batch-002', executionId: 'execution-001', sequence: 2, recipientCount: 3),
        ],
    );

    $result = (new PlanCampaignExecutionHandoff)->plan(new CampaignExecutionHandoffData(
        planningKey: 'plan-001',
        executionPlan: $executionPlan,
        requestedBy: 'queue',
        correlationId: 'corr-001',
        metadata: ['operation' => 'execution.handoff'],
    ));

    expect(new PlanCampaignExecutionHandoff)->toBeInstanceOf(PlansCampaignExecutionHandoffs::class)
        ->and($result->status)->toBe('ready')
        ->and($result->handoffId)->toStartWith('handoff-')
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'plan-001',
            'campaign_id' => 'campaign-001',
            'audience_id' => 'audience-001',
            'execution_id' => 'execution-001',
            'batch_count' => 2,
            'recipient_count' => 10,
            'requested_by' => 'queue',
            'correlation_id' => 'corr-001',
            'handoff_only' => true,
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

it('blocks execution handoff planning until execution batches exist', function () {
    $result = (new PlanCampaignExecutionHandoff)->plan(new CampaignExecutionHandoffData(
        planningKey: 'plan-001',
        executionPlan: new CampaignExecutionPlanData(
            execution: new CampaignExecutionData(
                id: 'execution-001',
                campaignId: 'campaign-001',
                audienceId: 'audience-001',
                status: 'planned',
            ),
        ),
    ));

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toContain('Execution handoff requires at least one planned batch.')
        ->and($result->effects->toArray()['issues_pay_codes'])->toBeFalse();
});

it('blocks execution handoff planning for non-planned execution states', function () {
    $result = (new PlanCampaignExecutionHandoff)->plan(new CampaignExecutionHandoffData(
        planningKey: 'plan-001',
        executionPlan: new CampaignExecutionPlanData(
            execution: new CampaignExecutionData(
                id: 'execution-001',
                campaignId: 'campaign-001',
                audienceId: 'audience-001',
                status: 'running',
            ),
            batches: [
                new CampaignBatchData(id: 'batch-001', executionId: 'execution-001', sequence: 1, recipientCount: 1),
            ],
        ),
    ));

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toContain('Execution handoff requires a planned execution.');
});

it('fails closed before planning a handoff without a planning key', function () {
    expect(fn () => (new PlanCampaignExecutionHandoff)->plan(new CampaignExecutionHandoffData(
        planningKey: '',
        executionPlan: new CampaignExecutionPlanData(
            execution: new CampaignExecutionData(
                id: 'execution-001',
                campaignId: 'campaign-001',
                audienceId: 'audience-001',
                status: 'planned',
            ),
        ),
    )))->toThrow(InvalidArgumentException::class, 'Campaign execution handoff planning key is required.');
});
