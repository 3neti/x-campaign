<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanSnapshotData;
use LBHurtado\XCampaign\Models\CampaignPlan;
use LBHurtado\XCampaign\Repositories\EloquentCampaignPlanSnapshotRepository;

it('persists and retrieves campaign plan snapshots through eloquent storage', function () {
    $this->artisan('migrate')->run();

    $repository = new EloquentCampaignPlanSnapshotRepository;
    $plan = new CampaignPlanData(
        campaign: new CampaignData(id: 'campaign-snapshot-001', name: 'Snapshot Campaign'),
        metadata: ['source' => 'phase-2e-test'],
    );

    $snapshot = $repository->put('planning-snapshot-001', new CampaignPlanSnapshotData(
        planningKey: 'planning-snapshot-001',
        plan: $plan,
        version: 3,
        checksum: 'checksum-003',
        metadata: ['stored' => true],
    ));

    $stored = $repository->get('planning-snapshot-001');

    expect($repository)->toBeInstanceOf(CampaignPlanSnapshotRepository::class)
        ->and($snapshot->planningKey)->toBe('planning-snapshot-001')
        ->and($stored)->toBeInstanceOf(CampaignPlanSnapshotData::class)
        ->and($stored?->planningKey)->toBe('planning-snapshot-001')
        ->and($stored?->plan->campaign->id)->toBe('campaign-snapshot-001')
        ->and($stored?->plan->campaign->name)->toBe('Snapshot Campaign')
        ->and($stored?->version)->toBe(3)
        ->and($stored?->checksum)->toBe('checksum-003')
        ->and(CampaignPlan::query()->where('planning_key', 'planning-snapshot-001')->exists())->toBeTrue();
});

it('reports durable persistence effects without execution or delivery side effects', function () {
    $effects = (new EloquentCampaignPlanSnapshotRepository)->effects();

    expect($effects)->toMatchArray([
        'persists' => true,
        'uses_database' => true,
        'queues_jobs' => false,
        'issues_pay_codes' => false,
        'sends_feedback' => false,
        'writes_journal' => false,
        'moves_money' => false,
    ]);
});
