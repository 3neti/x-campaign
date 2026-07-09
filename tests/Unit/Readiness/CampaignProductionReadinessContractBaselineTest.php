<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignProductionReadinessAssessments;
use LBHurtado\XCampaign\Data\CampaignOperationalReadinessData;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessChecklistData;

it('defines production readiness checklist data without release side effects', function () {
    $checklist = new CampaignProductionReadinessChecklistData(
        planningKey: 'planning-production',
        executionId: 'execution-production',
        operatorId: 'operator-production',
        operationalReadiness: new CampaignOperationalReadinessData(
            status: 'ready',
            summary: ['blockers' => []],
        ),
        metadata: ['request_id' => 'request-production'],
    );

    expect($checklist->planningKey)->toBe('planning-production')
        ->and($checklist->requiredChecks)->toBe([
            'operational_readiness',
            'package_boundaries',
            'host_handoff',
        ])
        ->and($checklist->metadata)->toMatchArray(['request_id' => 'request-production'])
        ->and($checklist->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines production readiness assessment data as a host-safe readiness result', function () {
    $assessment = new CampaignProductionReadinessAssessmentData(
        status: 'blocked',
        planningKey: 'planning-production',
        executionId: 'execution-production',
        operatorId: 'operator-production',
        checks: ['operational_readiness' => 'attention_required'],
        blockers: ['operational readiness is not ready'],
        metadata: ['source' => 'test'],
    );

    expect($assessment->status)->toBe('blocked')
        ->and($assessment->checks)->toMatchArray([
            'operational_readiness' => 'attention_required',
        ])
        ->and($assessment->blockers)->toBe(['operational readiness is not ready'])
        ->and($assessment->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a production readiness assessment builder contract', function () {
    expect(interface_exists(BuildsCampaignProductionReadinessAssessments::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignProductionReadinessAssessments::class, 'build'))->toBeTrue();
});
