<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignDeliveryHandoff;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignPortableCodeGeneration;
use LBHurtado\XCampaign\Contracts\CampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPortableCodeGenerationWorkspace;

function campaignDeliveryHandoffWorkspace(bool $withContacts = true): RepositoryBackedCampaignDeliveryHandoffWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Delivery Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-delivery', name: 'Delivery Audience'),
    );

    foreach (range(1, 2) as $recipientNumber) {
        $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
            $plan,
            'audience-delivery',
            new CampaignRecipientPlanningInputData(
                id: 'recipient-'.$recipientNumber,
                name: 'Recipient '.$recipientNumber,
                mobile: $withContacts ? '+63917111111'.$recipientNumber : null,
                externalReference: 'delivery-recipient-'.$recipientNumber,
            ),
        );
    }

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-delivery',
        audienceId: 'audience-delivery',
        correlationId: 'corr-delivery',
    ));

    $repository->put('planning-delivery', $plan);

    return new RepositoryBackedCampaignDeliveryHandoffWorkspace(
        repository: $repository,
        generationWorkspace: new RepositoryBackedCampaignPortableCodeGenerationWorkspace(
            repository: $repository,
            planner: new PlanCampaignPortableCodeGeneration,
        ),
        planner: new PlanCampaignDeliveryHandoff,
    );
}

it('plans delivery handoff from repository-backed campaign execution state', function () {
    $workspace = campaignDeliveryHandoffWorkspace();

    $result = $workspace->plan(
        planningKey: 'planning-delivery',
        executionId: 'execution-delivery',
        channel: 'sms',
        requestedBy: 'operator',
        correlationId: 'corr-delivery',
    );

    expect($workspace)->toBeInstanceOf(CampaignDeliveryHandoffWorkspace::class)
        ->and($result)->toBeInstanceOf(CampaignDeliveryHandoffWorkspaceResultData::class)
        ->and($result->status)->toBe('ready')
        ->and($result->planningKey)->toBe('planning-delivery')
        ->and($result->executionId)->toBe('execution-delivery')
        ->and($result->handoffResults)->toHaveCount(2)
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-delivery',
            'execution_id' => 'execution-delivery',
            'channel' => 'sms',
            'recipient_count' => 2,
            'handoff_count' => 2,
            'delivery_invoked' => false,
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

it('blocks delivery handoff workspace planning when recipient contacts are missing', function () {
    $workspace = campaignDeliveryHandoffWorkspace(withContacts: false);

    $result = $workspace->plan('planning-delivery', 'execution-delivery', channel: 'sms');

    expect($result->status)->toBe('blocked')
        ->and($result->handoffResults)->toHaveCount(2)
        ->and($result->blockers)->toContain('Delivery handoff requires a recipient mobile number for sms channel.');
});

it('fails closed for an unknown planning key before delivery handoff workspace planning', function () {
    $workspace = campaignDeliveryHandoffWorkspace();

    expect(fn () => $workspace->plan('missing-planning', 'execution-delivery'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for an execution outside the stored campaign plan before delivery handoff workspace planning', function () {
    $workspace = campaignDeliveryHandoffWorkspace();

    expect(fn () => $workspace->plan('planning-delivery', 'missing-execution'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign execution plan [missing-execution].');
});

