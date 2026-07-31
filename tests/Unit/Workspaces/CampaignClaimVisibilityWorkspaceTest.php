<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignClaimVisibility;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignPortableCodeGeneration;
use LBHurtado\XCampaign\Contracts\CampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;
use LBHurtado\XCampaign\Gateways\NullCampaignClaimStatusProvider;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPortableCodeGenerationWorkspace;

function campaignClaimVisibilityWorkspace(): RepositoryBackedCampaignClaimVisibilityWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Visibility Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-visibility', name: 'Visibility Audience'),
    );

    foreach (range(1, 2) as $recipientNumber) {
        $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
            $plan,
            'audience-visibility',
            new CampaignRecipientPlanningInputData(
                id: 'recipient-'.$recipientNumber,
                name: 'Recipient '.$recipientNumber,
                mobile: '+63917111111'.$recipientNumber,
                externalReference: 'visibility-recipient-'.$recipientNumber,
            ),
        );
    }

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-visibility',
        audienceId: 'audience-visibility',
        correlationId: 'corr-visibility',
    ));

    $repository->put('planning-visibility', $plan);

    return new RepositoryBackedCampaignClaimVisibilityWorkspace(
        repository: $repository,
        generationWorkspace: new RepositoryBackedCampaignPortableCodeGenerationWorkspace(
            repository: $repository,
            planner: new PlanCampaignPortableCodeGeneration,
        ),
        claimStatusProvider: new NullCampaignClaimStatusProvider,
        planner: new PlanCampaignClaimVisibility,
    );
}

it('plans claim visibility from repository-backed campaign execution state', function () {
    $workspace = campaignClaimVisibilityWorkspace();

    $result = $workspace->plan(
        planningKey: 'planning-visibility',
        executionId: 'execution-visibility',
        requestedBy: 'operator',
        correlationId: 'corr-visibility',
    );

    expect($workspace)->toBeInstanceOf(CampaignClaimVisibilityWorkspace::class)
        ->and($result)->toBeInstanceOf(CampaignClaimVisibilityWorkspaceResultData::class)
        ->and($result->status)->toBe('visible')
        ->and($result->planningKey)->toBe('planning-visibility')
        ->and($result->executionId)->toBe('execution-visibility')
        ->and($result->visibilityResults)->toHaveCount(2)
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-visibility',
            'execution_id' => 'execution-visibility',
            'recipient_count' => 2,
            'visibility_count' => 2,
            'claim_runtime_invoked' => false,
            'workspace' => 'repository-backed',
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

it('fails closed for an unknown planning key before claim visibility workspace planning', function () {
    $workspace = campaignClaimVisibilityWorkspace();

    expect(fn () => $workspace->plan('missing-planning', 'execution-visibility'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for an execution outside the stored campaign plan before claim visibility workspace planning', function () {
    $workspace = campaignClaimVisibilityWorkspace();

    expect(fn () => $workspace->plan('planning-visibility', 'missing-execution'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign execution plan [missing-execution].');
});
