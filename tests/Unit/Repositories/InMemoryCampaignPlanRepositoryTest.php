<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;

it('stores and retrieves campaign plans by caller supplied planning key without persistence', function () {
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Repository Baseline'));

    $stored = $repository->put('planning-001', $plan);

    expect($repository)->toBeInstanceOf(CampaignPlanRepository::class)
        ->and($stored)->toBe($plan)
        ->and($repository->get('planning-001'))->toBe($plan)
        ->and($repository->has('planning-001'))->toBeTrue()
        ->and($repository->effects())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('lists stored planning records as read-only in-memory values', function () {
    $repository = new InMemoryCampaignPlanRepository;

    $first = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'First Campaign'));
    $second = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Second Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-1', name: 'Audience One'),
    );

    $repository->put('first', $first);
    $repository->put('second', $second);

    $plans = $repository->all();

    expect($plans)->toHaveCount(2)
        ->and($plans['first'])->toBe($first)
        ->and($plans['second'])->toBe($second)
        ->and($plans['second']->audiences)->toHaveCount(1);
});

it('forgets campaign plans from memory without deleting persisted records', function () {
    $repository = new InMemoryCampaignPlanRepository;

    $repository->put('temporary', (new CreateCampaignPlan)->handle(
        new CampaignPlanningInputData(name: 'Temporary Campaign'),
    ));

    expect($repository->forget('temporary'))->toBeTrue()
        ->and($repository->has('temporary'))->toBeFalse()
        ->and($repository->get('temporary'))->toBeNull()
        ->and($repository->forget('temporary'))->toBeFalse();
});

it('rejects empty planning keys before storing a campaign plan', function () {
    $repository = new InMemoryCampaignPlanRepository;
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Invalid Key Campaign'));

    expect(fn () => $repository->put(' ', $plan))
        ->toThrow(InvalidArgumentException::class, 'Campaign planning repository key must not be empty.');
});
