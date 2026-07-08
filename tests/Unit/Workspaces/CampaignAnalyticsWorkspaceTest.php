<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignClaimVisibility;
use LBHurtado\XCampaign\Actions\PlanCampaignDeliveryHandoff;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignPortableCodeGeneration;
use LBHurtado\XCampaign\Contracts\CampaignAnalyticsWorkspace;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;
use LBHurtado\XCampaign\Gateways\NullCampaignClaimStatusProvider;
use LBHurtado\XCampaign\ReadModels\CampaignAnalyticsSnapshotBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignClaimVisibilitySummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignDeliveryHandoffSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignPortableCodeGenerationSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignSummaryReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAnalyticsWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPortableCodeGenerationWorkspace;

function campaignAnalyticsWorkspace(): RepositoryBackedCampaignAnalyticsWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Analytics Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-analytics', name: 'Analytics Audience'),
    );

    foreach (range(1, 2) as $recipientNumber) {
        $plan = (new AddRecipientToCampaignAudiencePlan)->handle(
            $plan,
            'audience-analytics',
            new CampaignRecipientPlanningInputData(
                id: 'recipient-'.$recipientNumber,
                name: 'Recipient '.$recipientNumber,
                mobile: '+63917222222'.$recipientNumber,
                externalReference: 'analytics-recipient-'.$recipientNumber,
            ),
        );
    }

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-analytics',
        audienceId: 'audience-analytics',
        correlationId: 'corr-analytics',
    ));

    $repository->put('planning-analytics', $plan);

    $generationWorkspace = new RepositoryBackedCampaignPortableCodeGenerationWorkspace(
        repository: $repository,
        planner: new PlanCampaignPortableCodeGeneration,
    );

    return new RepositoryBackedCampaignAnalyticsWorkspace(
        repository: $repository,
        campaignSummaries: new CampaignSummaryReadModel,
        generationWorkspace: $generationWorkspace,
        generationSummaries: new CampaignPortableCodeGenerationSummaryReadModel,
        deliveryWorkspace: new RepositoryBackedCampaignDeliveryHandoffWorkspace(
            repository: $repository,
            generationWorkspace: $generationWorkspace,
            planner: new PlanCampaignDeliveryHandoff,
        ),
        deliverySummaries: new CampaignDeliveryHandoffSummaryReadModel,
        claimVisibilityWorkspace: new RepositoryBackedCampaignClaimVisibilityWorkspace(
            repository: $repository,
            generationWorkspace: $generationWorkspace,
            claimStatusProvider: new NullCampaignClaimStatusProvider,
            planner: new PlanCampaignClaimVisibility,
        ),
        claimVisibilitySummaries: new CampaignClaimVisibilitySummaryReadModel,
        snapshots: new CampaignAnalyticsSnapshotBuilder,
    );
}

it('builds analytics snapshots from repository-backed campaign state', function () {
    $workspace = campaignAnalyticsWorkspace();

    $snapshot = $workspace->snapshot(
        planningKey: 'planning-analytics',
        executionId: 'execution-analytics',
        correlationId: 'corr-analytics',
        metadata: ['operator_scope' => 'analytics'],
    );

    expect($workspace)->toBeInstanceOf(CampaignAnalyticsWorkspace::class)
        ->and($snapshot)->toBeInstanceOf(CampaignAnalyticsSnapshotData::class)
        ->and($snapshot->status)->toBe('ready')
        ->and($snapshot->planningKey)->toBe('planning-analytics')
        ->and($snapshot->executionId)->toBe('execution-analytics')
        ->and($snapshot->recipientCount)->toBe(2)
        ->and($snapshot->generatedCount)->toBe(2)
        ->and($snapshot->deliveryReadyCount)->toBe(2)
        ->and($snapshot->claimVisibleCount)->toBe(2)
        ->and($snapshot->claimedCount)->toBe(0)
        ->and($snapshot->metadata)->toMatchArray([
            'operator_scope' => 'analytics',
            'workspace' => 'repository-backed',
            'analytics_only' => true,
            'read_only' => true,
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

it('fails closed for an unknown planning key before analytics aggregation', function () {
    expect(fn () => campaignAnalyticsWorkspace()->snapshot('missing-planning', 'execution-analytics'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for an execution outside the stored campaign plan before analytics aggregation', function () {
    expect(fn () => campaignAnalyticsWorkspace()->snapshot('planning-analytics', 'missing-execution'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign execution plan [missing-execution].');
});
