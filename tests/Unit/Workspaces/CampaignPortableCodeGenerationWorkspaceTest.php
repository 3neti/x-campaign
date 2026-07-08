<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignPortableCodeGeneration;
use LBHurtado\XCampaign\Contracts\CampaignPortableCodeGenerationWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPortableCodeGenerationWorkspace;

function campaignPortableCodeGenerationWorkspace(bool $withRecipients = true): RepositoryBackedCampaignPortableCodeGenerationWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Portable Code Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-portable', name: 'Portable Audience'),
    );

    if ($withRecipients) {
        foreach (range(1, 2) as $recipientNumber) {
            $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
                $plan,
                'audience-portable',
                new CampaignRecipientPlanningInputData(
                    id: 'recipient-'.$recipientNumber,
                    name: 'Recipient '.$recipientNumber,
                    externalReference: 'portable-recipient-'.$recipientNumber,
                ),
            );
        }
    }

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-portable',
        audienceId: 'audience-portable',
        correlationId: 'corr-portable',
    ));

    $repository->put('planning-portable', $plan);

    return new RepositoryBackedCampaignPortableCodeGenerationWorkspace(
        repository: $repository,
        planner: new PlanCampaignPortableCodeGeneration,
    );
}

it('plans portable code generation from repository-backed campaign execution state', function () {
    $workspace = campaignPortableCodeGenerationWorkspace();

    $result = $workspace->plan(
        planningKey: 'planning-portable',
        executionId: 'execution-portable',
        batchId: 'batch-1',
        correlationId: 'corr-portable',
        metadata: ['source' => 'workspace'],
    );

    expect($workspace)->toBeInstanceOf(CampaignPortableCodeGenerationWorkspace::class)
        ->and($result)->toBeInstanceOf(CampaignPortableCodeGenerationWorkspaceResultData::class)
        ->and($result->status)->toBe('planned')
        ->and($result->planningKey)->toBe('planning-portable')
        ->and($result->executionId)->toBe('execution-portable')
        ->and($result->generationResults)->toHaveCount(2)
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-portable',
            'campaign_id' => null,
            'audience_id' => 'audience-portable',
            'execution_id' => 'execution-portable',
            'recipient_count' => 2,
            'generation_count' => 2,
            'gateway_invoked' => false,
            'workspace' => 'repository-backed',
        ])
        ->and($result->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
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

it('blocks portable code generation workspace planning when the audience has no recipients', function () {
    $workspace = campaignPortableCodeGenerationWorkspace(withRecipients: false);

    $result = $workspace->plan('planning-portable', 'execution-portable');

    expect($result->status)->toBe('blocked')
        ->and($result->generationResults)->toBe([])
        ->and($result->blockers)->toBe([
            'Portable code generation requires at least one planned recipient.',
        ])
        ->and($result->metadata)->toMatchArray([
            'recipient_count' => 0,
            'generation_count' => 0,
            'gateway_invoked' => false,
        ]);
});

it('fails closed for an unknown planning key before portable code generation workspace planning', function () {
    $workspace = campaignPortableCodeGenerationWorkspace();

    expect(fn () => $workspace->plan('missing-planning', 'execution-portable'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for an execution outside the stored campaign plan before portable code generation workspace planning', function () {
    $workspace = campaignPortableCodeGenerationWorkspace();

    expect(fn () => $workspace->plan('planning-portable', 'missing-execution'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign execution plan [missing-execution].');
});
