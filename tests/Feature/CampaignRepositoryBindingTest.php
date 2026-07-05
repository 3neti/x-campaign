<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;

it('binds the campaign planning repository contract to the in-memory baseline', function () {
    expect(app(CampaignPlanRepository::class))->toBeInstanceOf(InMemoryCampaignPlanRepository::class);
});

