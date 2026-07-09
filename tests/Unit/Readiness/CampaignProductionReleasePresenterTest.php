<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignProductionReleases;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReleaseData;
use LBHurtado\XCampaign\ReadModels\CampaignProductionReleasePresenter;

it('presents ready assessments as releasable host-safe release envelopes', function () {
    $release = (new CampaignProductionReleasePresenter)->present(new CampaignProductionReadinessAssessmentData(
        status: 'ready',
        planningKey: 'planning-production',
        executionId: 'execution-production',
        operatorId: 'operator-production',
        checks: [
            'operational_readiness' => 'ready',
            'package_boundaries' => 'read_only',
            'host_handoff' => 'required',
        ],
        metadata: ['request_id' => 'request-production'],
    ));

    expect($release)->toBeInstanceOf(CampaignProductionReleaseData::class)
        ->and($release->status)->toBe('releasable')
        ->and($release->summary)->toMatchArray([
            'planning_key' => 'planning-production',
            'execution_id' => 'execution-production',
            'operator_id' => 'operator-production',
            'readiness_status' => 'ready',
        ])
        ->and($release->meta)->toMatchArray([
            'request_id' => 'request-production',
            'source' => 'campaign-production-release-presenter',
            'read_only' => true,
            'deploys' => false,
            'writes_environment' => false,
            'starts_workers' => false,
        ])
        ->and($release->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('presents blocked assessments without hiding blockers', function () {
    $release = (new CampaignProductionReleasePresenter)->present(new CampaignProductionReadinessAssessmentData(
        status: 'blocked',
        planningKey: 'planning-production',
        executionId: 'execution-production',
        operatorId: 'operator-production',
        blockers: ['operator review required'],
    ));

    expect($release->status)->toBe('blocked')
        ->and($release->summary)->toMatchArray([
            'blockers' => ['operator review required'],
        ]);
});

it('implements the production release presenter contract', function () {
    expect(new CampaignProductionReleasePresenter)
        ->toBeInstanceOf(PresentsCampaignProductionReleases::class);
});
