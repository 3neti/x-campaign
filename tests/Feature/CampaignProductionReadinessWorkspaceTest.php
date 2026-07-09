<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignProductionReadinessWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

it('assesses production readiness from repository-backed operational state without release side effects', function () {
    $repository = app(CampaignPlanRepository::class);
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Production Readiness Campaign',
        owner: 'operator-production',
    ));
    $plan = (new AddAudienceToCampaignPlan)->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-production',
        name: 'Production Audience',
    ));
    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-production',
        audienceId: 'audience-production',
    ));

    $repository->put('planning-production', $plan);

    $assessment = app(CampaignProductionReadinessWorkspace::class)->assess(
        planningKey: 'planning-production',
        executionId: 'execution-production',
        operatorId: 'operator-production',
        channel: 'sms',
        correlationId: 'correlation-production',
        metadata: ['request_id' => 'request-production'],
    );

    expect($assessment->planningKey)->toBe('planning-production')
        ->and($assessment->executionId)->toBe('execution-production')
        ->and($assessment->operatorId)->toBe('operator-production')
        ->and($assessment->checks)->toHaveKeys([
            'operational_readiness',
            'package_boundaries',
            'host_handoff',
        ])
        ->and($assessment->metadata)->toMatchArray([
            'request_id' => 'request-production',
            'workspace' => 'repository-backed',
            'production_readiness_only' => true,
            'source' => 'campaign-production-readiness-assessment-builder',
        ])
        ->and($assessment->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});
