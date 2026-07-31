<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\ArchivesCampaignPlans;
use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

it('binds campaign planning action contracts to in-memory implementations', function () {
    expect(app(CreatesCampaignPlans::class)->handle(new CampaignPlanningInputData(name: 'Planning Campaign'))->campaign->name)
        ->toBe('Planning Campaign')
        ->and(app(UpdatesCampaignPlans::class))->toBeInstanceOf(UpdatesCampaignPlans::class)
        ->and(app(SchedulesCampaignPlans::class))->toBeInstanceOf(SchedulesCampaignPlans::class)
        ->and(app(ArchivesCampaignPlans::class))->toBeInstanceOf(ArchivesCampaignPlans::class);
});
