<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Contracts\CampaignHostIntegrationWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

it('builds host integration manifests from repository-backed planning context without host infrastructure', function () {
    $repository = app(CampaignPlanRepository::class);
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Host Integration Campaign',
        owner: 'operator-host',
    ));
    $plan = (new AddAudienceToCampaignPlan)->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-host',
        name: 'Host Audience',
    ));
    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-host',
        audienceId: 'audience-host',
    ));

    $repository->put('planning-host', $plan);

    $manifest = app(CampaignHostIntegrationWorkspace::class)->manifest(
        planningKey: 'planning-host',
        executionId: 'execution-host',
        operatorId: 'operator-host',
        channel: 'sms',
        correlationId: 'correlation-host',
        metadata: ['request_id' => 'request-host'],
    );

    expect($manifest->status)->toBe('available')
        ->and($manifest->planningKey)->toBe('planning-host')
        ->and($manifest->capabilities)->toHaveKeys([
            'planning_workspace',
            'cockpit_summary',
            'operational_monitor',
            'production_readiness',
        ])
        ->and($manifest->metadata)->toMatchArray([
            'request_id' => 'request-host',
            'workspace' => 'repository-backed',
            'host_integration_only' => true,
            'source' => 'campaign-host-integration-manifest-builder',
        ])
        ->and(app(CampaignHostIntegrationWorkspace::class)->effects())->toMatchArray([
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
