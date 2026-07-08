<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanSnapshotData;

it('defines a durable snapshot DTO without changing the campaign plan repository contract', function () {
    $plan = new CampaignPlanData(
        campaign: new CampaignData(id: 'campaign-persistence-001', name: 'Persistence Campaign'),
        metadata: ['source' => 'phase-2b-test'],
    );

    $snapshot = new CampaignPlanSnapshotData(
        planningKey: 'planning-persistence-001',
        plan: $plan,
        version: 1,
        checksum: 'checksum-001',
        metadata: ['storage' => 'planned'],
    );

    expect($snapshot->planningKey)->toBe('planning-persistence-001')
        ->and($snapshot->plan)->toBe($plan)
        ->and($snapshot->version)->toBe(1)
        ->and($snapshot->checksum)->toBe('checksum-001')
        ->and($snapshot->metadata)->toMatchArray(['storage' => 'planned'])
        ->and(interface_exists(CampaignPlanRepository::class))->toBeTrue();
});

it('defines a snapshot repository seam for future durable storage', function () {
    expect(interface_exists(CampaignPlanSnapshotRepository::class))->toBeTrue();

    $reflection = new ReflectionClass(CampaignPlanSnapshotRepository::class);

    expect($reflection->getMethod('put')->getReturnType()?->getName())->toBe(CampaignPlanSnapshotData::class)
        ->and($reflection->getMethod('get')->getReturnType()?->getName())->toBe(CampaignPlanSnapshotData::class)
        ->and($reflection->getMethod('get')->getReturnType()?->allowsNull())->toBeTrue()
        ->and($reflection->getMethod('has')->getReturnType()?->getName())->toBe('bool')
        ->and($reflection->getMethod('forget')->getReturnType()?->getName())->toBe('bool')
        ->and($reflection->getMethod('effects')->getReturnType()?->getName())->toBe('array');
});

it('defines persistence effect metadata separately from money movement or delivery', function () {
    $effects = new CampaignPersistenceEffectData(
        persists: true,
        usesDatabase: true,
    );

    expect($effects->persists)->toBeTrue()
        ->and($effects->usesDatabase)->toBeTrue()
        ->and($effects->queuesJobs)->toBeFalse()
        ->and($effects->issuesPortableCodes)->toBeFalse()
        ->and($effects->sendsFeedback)->toBeFalse()
        ->and($effects->writesAuditLog)->toBeFalse()
        ->and($effects->movesMoney)->toBeFalse()
        ->and($effects->toArray())->toMatchArray([
            'persists' => true,
            'uses_database' => true,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});
