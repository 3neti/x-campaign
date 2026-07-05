<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\ArchiveCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\ScheduleCampaignPlan;
use LBHurtado\XCampaign\Actions\UpdateCampaignPlan;
use LBHurtado\XCampaign\Contracts\ArchivesCampaignPlans;
use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

it('creates a campaign plan in memory without persistence or execution', function () {
    $action = new CreateCampaignPlan;

    $plan = $action->handle(new CampaignPlanningInputData(
        name: 'Educational Assistance 2027',
        description: 'Scholar assistance release',
        featureProfile: 'baseline',
        owner: 'operations',
        issuer: 'issuer-1',
        scheduledAt: '2027-01-15T09:00:00+08:00',
        metadata: ['program_blueprint_reference' => 'blueprint-1'],
    ));

    expect($action)->toBeInstanceOf(CreatesCampaignPlans::class)
        ->and($plan)->toBeInstanceOf(CampaignPlanData::class)
        ->and($plan->campaign->name)->toBe('Educational Assistance 2027')
        ->and($plan->campaign->status)->toBe('draft')
        ->and($plan->audiences)->toBe([])
        ->and($plan->executions)->toBe([])
        ->and($plan->effects)->toBe([
            'persists' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($plan->metadata['source'])->toBe('in-memory-planning');
});

it('updates a campaign plan in memory while preserving planning-only semantics', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Payroll Batch 2027-01',
        owner: 'operations',
    ));

    $updated = (new UpdateCampaignPlan)->handle($plan, new CampaignPlanningInputData(
        name: 'Payroll Batch 2027-01 Updated',
        description: 'Updated planning description',
        metadata: ['batch_reference' => 'batch-a'],
    ));

    expect(new UpdateCampaignPlan)->toBeInstanceOf(UpdatesCampaignPlans::class)
        ->and($updated->campaign->name)->toBe('Payroll Batch 2027-01 Updated')
        ->and($updated->campaign->description)->toBe('Updated planning description')
        ->and($updated->campaign->owner)->toBe('operations')
        ->and($updated->campaign->metadata)->toBe(['batch_reference' => 'batch-a'])
        ->and($updated->effects['persists'])->toBeFalse()
        ->and($updated->effects['issues_pay_codes'])->toBeFalse();
});

it('schedules a draft campaign plan without queueing or dispatching execution', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Disaster Relief Campaign',
    ));

    $scheduled = (new ScheduleCampaignPlan)->handle($plan, '2027-02-01T10:30:00+08:00');

    expect(new ScheduleCampaignPlan)->toBeInstanceOf(SchedulesCampaignPlans::class)
        ->and($scheduled->campaign->status)->toBe('scheduled')
        ->and($scheduled->campaign->scheduledAt)->toBe('2027-02-01T10:30:00+08:00')
        ->and($scheduled->metadata['scheduled_without_dispatch'])->toBeTrue()
        ->and($scheduled->effects['persists'])->toBeFalse()
        ->and($scheduled->effects['issues_pay_codes'])->toBeFalse();
});

it('archives a campaign plan without deleting records or mutating external state', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Old Campaign',
    ));

    $archived = (new ArchiveCampaignPlan)->handle($plan, 'operator cleanup');

    expect(new ArchiveCampaignPlan)->toBeInstanceOf(ArchivesCampaignPlans::class)
        ->and($archived->campaign->status)->toBe('archived')
        ->and($archived->metadata['archive_reason'])->toBe('operator cleanup')
        ->and($archived->effects)->toMatchArray([
            'persists' => false,
            'deletes_records' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'moves_money' => false,
        ]);
});

