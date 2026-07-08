<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Contracts\CampaignCockpitWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

it('builds a cockpit summary from repository-backed campaign planning state without mutation', function () {
    $repository = app(CampaignPlanRepository::class);
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Cockpit Workspace Campaign',
        owner: 'operator-cockpit',
    ));

    $plan = (new AddAudienceToCampaignPlan)->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-cockpit-workspace',
        name: 'Cockpit Audience',
    ));

    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-cockpit-workspace',
        audienceId: 'audience-cockpit-workspace',
    ));

    $repository->put('planning-cockpit-workspace', $plan);

    $summary = app(CampaignCockpitWorkspace::class)->summary(
        planningKey: 'planning-cockpit-workspace',
        executionId: 'execution-cockpit-workspace',
        operatorId: 'operator-cockpit',
        channel: 'sms',
        correlationId: 'correlation-cockpit',
        metadata: ['request_id' => 'request-cockpit'],
    );

    expect($summary->planningKey)->toBe('planning-cockpit-workspace')
        ->and($summary->executionId)->toBe('execution-cockpit-workspace')
        ->and($summary->operatorId)->toBe('operator-cockpit')
        ->and($summary->cards)->toHaveKeys(['campaign', 'analytics', 'export'])
        ->and($summary->metadata)->toMatchArray([
            'request_id' => 'request-cockpit',
            'workspace' => 'repository-backed',
            'cockpit_only' => true,
            'source' => 'campaign-cockpit-summary-builder',
        ])
        ->and($summary->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});
