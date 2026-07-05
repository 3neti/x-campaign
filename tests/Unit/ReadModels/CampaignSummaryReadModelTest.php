<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionBatches;
use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;
use LBHurtado\XCampaign\ReadModels\CampaignSummaryReadModel;

it('builds a side effect free campaign summary from an in-memory campaign plan', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Relief Distribution',
        featureProfile: 'relief',
        owner: 'operations',
    ));

    $plan = (new AddAudienceToCampaignPlan)->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-1',
        name: 'Barangay 1',
    ));

    foreach (range(1, 3) as $recipientNumber) {
        $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
            $plan,
            'audience-1',
            new CampaignRecipientPlanningInputData(
                name: 'Recipient '.$recipientNumber,
                mobile: '+63 917 000 000'.$recipientNumber,
                externalReference: 'recipient-'.$recipientNumber,
            ),
        );
    }

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-1',
        audienceId: 'audience-1',
        scheduledAt: '2027-04-01T09:00:00+08:00',
    ));

    $plan = (new PlanCampaignExecutionBatches)->handle($plan, 'execution-1', 2);

    $summary = (new CampaignSummaryReadModel)->fromPlan($plan);

    expect(new CampaignSummaryReadModel)->toBeInstanceOf(BuildsCampaignSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignSummaryData::class)
        ->and($summary->campaignId)->toBeNull()
        ->and($summary->name)->toBe('Relief Distribution')
        ->and($summary->status)->toBe('draft')
        ->and($summary->featureProfile)->toBe('relief')
        ->and($summary->audienceCount)->toBe(1)
        ->and($summary->recipientCount)->toBe(3)
        ->and($summary->executionCount)->toBe(1)
        ->and($summary->batchCount)->toBe(2)
        ->and($summary->plannedRecipientCount)->toBe(3)
        ->and($summary->effects)->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($summary->metadata['source'])->toBe('campaign-summary-read-model');
});

it('builds audience and execution summary rows without exposing recipient detail records', function () {
    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Education Distribution')),
        new CampaignAudiencePlanningInputData(id: 'audience-students', name: 'Students'),
    );

    $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
        $plan,
        'audience-students',
        new CampaignRecipientPlanningInputData(name: 'Student One', email: 'STUDENT@EXAMPLE.TEST'),
    );

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-students',
        audienceId: 'audience-students',
    ));

    $summary = (new CampaignSummaryReadModel)->fromPlan($plan);

    expect($summary->audiences)->toHaveCount(1)
        ->and($summary->audiences[0]->audienceId)->toBe('audience-students')
        ->and($summary->audiences[0]->name)->toBe('Students')
        ->and($summary->audiences[0]->recipientCount)->toBe(1)
        ->and($summary->audiences[0]->metadata)->not->toHaveKey('recipients')
        ->and($summary->executions)->toHaveCount(1)
        ->and($summary->executions[0]->executionId)->toBe('execution-students')
        ->and($summary->executions[0]->audienceId)->toBe('audience-students')
        ->and($summary->executions[0]->status)->toBe('planned')
        ->and($summary->executions[0]->batchCount)->toBe(0)
        ->and($summary->executions[0]->plannedRecipientCount)->toBe(1);
});

it('returns empty read-model counts for a draft campaign without audiences or executions', function () {
    $summary = (new CampaignSummaryReadModel)->fromPlan(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Empty Campaign')),
    );

    expect($summary->audienceCount)->toBe(0)
        ->and($summary->recipientCount)->toBe(0)
        ->and($summary->executionCount)->toBe(0)
        ->and($summary->batchCount)->toBe(0)
        ->and($summary->plannedRecipientCount)->toBe(0)
        ->and($summary->audiences)->toBe([])
        ->and($summary->executions)->toBe([]);
});
