<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitConsumptionMaps;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitConsumptionMapBuilder;

it('binds the cockpit consumption map builder contract', function () {
    expect(app(BuildsCampaignCockpitConsumptionMaps::class))
        ->toBeInstanceOf(CampaignCockpitConsumptionMapBuilder::class);
});
