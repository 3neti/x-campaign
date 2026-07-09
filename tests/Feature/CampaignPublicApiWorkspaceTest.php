<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPublicApiWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

it('builds public api descriptors from repository-backed planning context without host infrastructure', function () {
    $repository = app(CampaignPlanRepository::class);
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Public API Campaign',
        owner: 'operator-api',
    ));
    $plan = (new AddAudienceToCampaignPlan)->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-api',
        name: 'API Audience',
    ));
    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-api',
        audienceId: 'audience-api',
    ));

    $repository->put('planning-api', $plan);

    $descriptor = app(CampaignPublicApiWorkspace::class)->descriptor(
        planningKey: 'planning-api',
        executionId: 'execution-api',
        operatorId: 'operator-api',
        apiVersion: 'v1',
        metadata: ['request_id' => 'request-api'],
    );

    expect($descriptor->status)->toBe('described')
        ->and($descriptor->planningKey)->toBe('planning-api')
        ->and($descriptor->endpoints)->toHaveKeys([
            'campaign.summary',
            'campaign.cockpit',
            'campaign.production_readiness',
            'campaign.host_integration',
        ])
        ->and($descriptor->metadata)->toMatchArray([
            'request_id' => 'request-api',
            'workspace' => 'repository-backed',
            'public_api_only' => true,
            'planning_key_exists' => true,
            'source' => 'campaign-public-api-descriptor-builder',
        ])
        ->and(app(CampaignPublicApiWorkspace::class)->effects())->toMatchArray([
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
