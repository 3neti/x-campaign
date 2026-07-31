<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionBatches;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionBatches;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutions;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

it('plans a campaign execution in memory without queueing or issuing pay codes', function () {
    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Educational Assistance 2027')),
        new CampaignAudiencePlanningInputData(id: 'audience-scholars', name: 'All Scholars'),
    );

    $planned = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-001',
        audienceId: 'audience-scholars',
        scheduledAt: '2027-03-01T09:00:00+08:00',
        correlationId: 'campaign-correlation-1',
        metadata: ['operator' => 'ops'],
    ));

    expect(new PlanCampaignExecution)->toBeInstanceOf(PlansCampaignExecutions::class)
        ->and($planned->executions)->toHaveCount(1)
        ->and($planned->executions[0])->toBeInstanceOf(CampaignExecutionPlanData::class)
        ->and($planned->executions[0]->execution->id)->toBe('execution-001')
        ->and($planned->executions[0]->execution->audienceId)->toBe('audience-scholars')
        ->and($planned->executions[0]->execution->status)->toBe('planned')
        ->and($planned->executions[0]->batches)->toBe([])
        ->and($planned->effects)->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($planned->metadata['execution_planning'])->toBe('in-memory');
});

it('fails closed when planning execution for an unknown audience', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'No Audience Campaign'));

    expect(fn () => (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        audienceId: 'missing-audience',
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});

it('plans campaign execution batches from recipient count without queueing jobs', function () {
    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Relief Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-relief', name: 'Relief Beneficiaries'),
    );

    foreach (range(1, 5) as $recipientNumber) {
        $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
            $plan,
            'audience-relief',
            new CampaignRecipientPlanningInputData(
                name: 'Recipient '.$recipientNumber,
                externalReference: 'recipient-'.$recipientNumber,
            ),
        );
    }

    $planned = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-relief',
        audienceId: 'audience-relief',
    ));

    $batched = (new PlanCampaignExecutionBatches)->handle($planned, 'execution-relief', 2);

    expect(new PlanCampaignExecutionBatches)->toBeInstanceOf(PlansCampaignExecutionBatches::class)
        ->and($batched->executions[0]->batches)->toHaveCount(3)
        ->and($batched->executions[0]->batches[0]->sequence)->toBe(1)
        ->and($batched->executions[0]->batches[0]->recipientCount)->toBe(2)
        ->and($batched->executions[0]->batches[1]->sequence)->toBe(2)
        ->and($batched->executions[0]->batches[1]->recipientCount)->toBe(2)
        ->and($batched->executions[0]->batches[2]->sequence)->toBe(3)
        ->and($batched->executions[0]->batches[2]->recipientCount)->toBe(1)
        ->and($batched->executions[0]->metadata['batch_size'])->toBe(2)
        ->and($batched->effects['queues_jobs'])->toBeFalse()
        ->and($batched->effects['issues_pay_codes'])->toBeFalse();
});

it('rejects invalid batch sizes before planning batches', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Invalid Batch Test'));

    expect(fn () => (new PlanCampaignExecutionBatches)->handle($plan, 'execution-1', 0))
        ->toThrow(InvalidArgumentException::class, 'Campaign execution batch size must be greater than zero.');
});
