<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignProductionReadinessAssessments;
use LBHurtado\XCampaign\Data\CampaignOperationalReadinessData;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessChecklistData;
use LBHurtado\XCampaign\ReadModels\CampaignProductionReadinessAssessmentBuilder;

it('marks a checklist ready when operational readiness and package handoff checks are ready', function () {
    $assessment = (new CampaignProductionReadinessAssessmentBuilder)->build(new CampaignProductionReadinessChecklistData(
        planningKey: 'planning-production',
        executionId: 'execution-production',
        operatorId: 'operator-production',
        operationalReadiness: new CampaignOperationalReadinessData(
            status: 'ready',
            summary: ['blockers' => []],
        ),
        metadata: ['request_id' => 'request-production'],
    ));

    expect($assessment->status)->toBe('ready')
        ->and($assessment->checks)->toMatchArray([
            'operational_readiness' => 'ready',
            'package_boundaries' => 'read_only',
            'host_handoff' => 'required',
        ])
        ->and($assessment->blockers)->toBe([])
        ->and($assessment->metadata)->toMatchArray([
            'request_id' => 'request-production',
            'source' => 'campaign-production-readiness-assessment-builder',
            'read_only' => true,
            'deploys' => false,
            'writes_environment' => false,
            'starts_workers' => false,
        ]);
});

it('blocks production readiness when operational readiness is not ready', function () {
    $assessment = (new CampaignProductionReadinessAssessmentBuilder)->build(new CampaignProductionReadinessChecklistData(
        planningKey: 'planning-production',
        executionId: 'execution-production',
        operatorId: 'operator-production',
        operationalReadiness: new CampaignOperationalReadinessData(
            status: 'attention_required',
            summary: ['blockers' => ['operator review required']],
        ),
    ));

    expect($assessment->status)->toBe('blocked')
        ->and($assessment->checks)->toMatchArray([
            'operational_readiness' => 'attention_required',
        ])
        ->and($assessment->blockers)->toBe([
            'operator review required',
            'operational readiness is not ready',
        ]);
});

it('implements the production readiness assessment builder contract', function () {
    expect(new CampaignProductionReadinessAssessmentBuilder)
        ->toBeInstanceOf(BuildsCampaignProductionReadinessAssessments::class);
});
