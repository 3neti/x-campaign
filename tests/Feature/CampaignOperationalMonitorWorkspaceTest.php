<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Contracts\CampaignOperationalMonitorWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

it('builds operational health snapshots from repository-backed cockpit state without exporting metrics', function () {
    $repository = app(CampaignPlanRepository::class);
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Operational Monitor Campaign',
        owner: 'operator-monitor',
    ));
    $plan = (new AddAudienceToCampaignPlan)->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-monitor',
        name: 'Monitor Audience',
    ));
    $plan = (new PlanCampaignExecution)->handle($plan, new CampaignExecutionPlanningInputData(
        id: 'execution-monitor',
        audienceId: 'audience-monitor',
    ));

    $repository->put('planning-monitor', $plan);

    $snapshot = app(CampaignOperationalMonitorWorkspace::class)->snapshot(
        planningKey: 'planning-monitor',
        executionId: 'execution-monitor',
        operatorId: 'operator-monitor',
        channel: 'sms',
        correlationId: 'correlation-monitor',
        metadata: ['request_id' => 'request-monitor'],
    );

    expect($snapshot->planningKey)->toBe('planning-monitor')
        ->and($snapshot->executionId)->toBe('execution-monitor')
        ->and($snapshot->operatorId)->toBe('operator-monitor')
        ->and($snapshot->checks)->toHaveKeys(['cockpit', 'api_response'])
        ->and($snapshot->metadata)->toMatchArray([
            'request_id' => 'request-monitor',
            'workspace' => 'repository-backed',
            'operational_monitor_only' => true,
            'source' => 'campaign-operational-health-snapshot-builder',
        ])
        ->and($snapshot->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});
