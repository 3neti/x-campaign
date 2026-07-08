<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionHandoffs;
use LBHurtado\XCampaign\Data\CampaignBatchData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;

it('defines an execution handoff DTO without execution side effects', function () {
    $executionPlan = new CampaignExecutionPlanData(
        execution: new CampaignExecutionData(
            id: 'exec-001',
            campaignId: 'campaign-001',
            audienceId: 'aud-001',
            status: 'planned',
            correlationId: 'corr-001',
        ),
        batches: [
            new CampaignBatchData(id: 'batch-001', executionId: 'exec-001', sequence: 1, recipientCount: 10),
        ],
    );

    $handoff = new CampaignExecutionHandoffData(
        planningKey: 'plan-001',
        executionPlan: $executionPlan,
        requestedBy: 'queue',
        correlationId: 'corr-001',
        metadata: ['operation' => 'execution.handoff'],
    );

    expect($handoff->planningKey)->toBe('plan-001')
        ->and($handoff->executionPlan)->toBe($executionPlan)
        ->and($handoff->requestedBy)->toBe('queue')
        ->and($handoff->correlationId)->toBe('corr-001')
        ->and($handoff->metadata)->toBe(['operation' => 'execution.handoff'])
        ->and($handoff->effects)->toBeInstanceOf(CampaignPersistenceEffectData::class)
        ->and($handoff->effects->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an execution handoff result envelope that remains handoff-only by default', function () {
    $handoff = new CampaignExecutionHandoffData(
        planningKey: 'plan-001',
        executionPlan: new CampaignExecutionPlanData(
            execution: new CampaignExecutionData(id: 'exec-001', campaignId: 'campaign-001', audienceId: 'aud-001'),
        ),
        correlationId: 'corr-001',
    );

    $result = new CampaignExecutionHandoffResultData(
        status: 'ready',
        handoffId: 'handoff-001',
        handoff: $handoff,
        blockers: [],
        metadata: ['source' => 'phase-4b'],
    );

    expect($result->status)->toBe('ready')
        ->and($result->handoffId)->toBe('handoff-001')
        ->and($result->handoff)->toBe($handoff)
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toBe(['source' => 'phase-4b'])
        ->and($result->effects->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines an execution handoff planning contract before execution handoff implementation exists', function () {
    expect(interface_exists(PlansCampaignExecutionHandoffs::class))->toBeTrue()
        ->and(method_exists(PlansCampaignExecutionHandoffs::class, 'plan'))->toBeTrue();
});
