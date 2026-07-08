<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionBatches;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionHandoff;
use LBHurtado\XCampaign\Contracts\CampaignExecutionHandoffWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignExecutionHandoffWorkspace;

function campaignExecutionHandoffWorkspace(): RepositoryBackedCampaignExecutionHandoffWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Handoff Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-handoff', name: 'Handoff Audience'),
    );

    foreach (range(1, 3) as $recipientNumber) {
        $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
            $plan,
            'audience-handoff',
            new CampaignRecipientPlanningInputData(
                name: 'Recipient '.$recipientNumber,
                externalReference: 'recipient-'.$recipientNumber,
            ),
        );
    }

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-handoff',
        audienceId: 'audience-handoff',
        correlationId: 'corr-handoff',
    ));

    $plan = (new PlanCampaignExecutionBatches)->handle($plan, 'execution-handoff', 2);

    $repository->put('planning-handoff', $plan);

    return new RepositoryBackedCampaignExecutionHandoffWorkspace(
        repository: $repository,
        planner: new PlanCampaignExecutionHandoff,
    );
}

it('plans execution handoff from repository-backed campaign execution state', function () {
    $workspace = campaignExecutionHandoffWorkspace();

    $result = $workspace->plan(
        planningKey: 'planning-handoff',
        executionId: 'execution-handoff',
        requestedBy: 'operator',
        correlationId: 'corr-handoff',
        metadata: ['source' => 'workspace'],
    );

    expect($workspace)->toBeInstanceOf(CampaignExecutionHandoffWorkspace::class)
        ->and($result->status)->toBe('ready')
        ->and($result->handoff->planningKey)->toBe('planning-handoff')
        ->and($result->handoff->executionPlan->execution->id)->toBe('execution-handoff')
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-handoff',
            'execution_id' => 'execution-handoff',
            'batch_count' => 2,
            'recipient_count' => 3,
            'requested_by' => 'operator',
            'handoff_only' => true,
        ])
        ->and($workspace->effects())->toMatchArray([
            'integrates_repository' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('fails closed for an unknown planning key before handoff planning', function () {
    $workspace = campaignExecutionHandoffWorkspace();

    expect(fn () => $workspace->plan('missing-planning', 'execution-handoff'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for an execution outside the stored campaign plan', function () {
    $workspace = campaignExecutionHandoffWorkspace();

    expect(fn () => $workspace->plan('planning-handoff', 'missing-execution'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign execution plan [missing-execution].');
});
