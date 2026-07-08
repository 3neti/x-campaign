<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudienceData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Repositories\EloquentCampaignPlanRepository;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;

function phaseTwoFPlan(string $id): CampaignPlanData
{
    return new CampaignPlanData(
        campaign: new CampaignData(
            id: 'campaign-'.$id,
            name: 'Campaign '.$id,
            status: 'draft',
        ),
        audiences: [
            new CampaignAudiencePlanData(
                audience: new CampaignAudienceData(
                    id: 'audience-'.$id,
                    name: 'Audience '.$id,
                    status: 'draft',
                ),
            ),
        ],
        metadata: ['source' => 'phase-2f'],
    );
}

it('preserves campaign plan repository behavior across in-memory and eloquent implementations', function () {
    $this->artisan('migrate:fresh')->run();

    $repositories = [
        'memory' => new InMemoryCampaignPlanRepository,
        'eloquent' => new EloquentCampaignPlanRepository,
    ];

    foreach ($repositories as $label => $repository) {
        expect($repository)->toBeInstanceOf(CampaignPlanRepository::class);

        $key = 'planning-parity-'.$label;
        $plan = phaseTwoFPlan($label);

        expect($repository->has($key))->toBeFalse()
            ->and($repository->get($key))->toBeNull();

        $stored = $repository->put($key, $plan);
        $retrieved = $repository->get($key);

        expect($stored)->toBe($plan)
            ->and($repository->has($key))->toBeTrue()
            ->and($retrieved)->toBeInstanceOf(CampaignPlanData::class)
            ->and($retrieved?->campaign->id)->toBe('campaign-'.$label)
            ->and($retrieved?->campaign->name)->toBe('Campaign '.$label)
            ->and($retrieved?->audiences)->toHaveCount(1)
            ->and($repository->all())->toHaveKey($key)
            ->and($repository->forget($key))->toBeTrue()
            ->and($repository->has($key))->toBeFalse()
            ->and($repository->forget($key))->toBeFalse();
    }
});

it('keeps durable repository effects explicit and free of runtime side effects', function () {
    $effects = (new EloquentCampaignPlanRepository)->effects();

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
