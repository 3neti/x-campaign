<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitSummaryBuilder;

it('binds cockpit summary building to the read-only builder', function () {
    expect(app(BuildsCampaignCockpitSummaries::class))
        ->toBeInstanceOf(CampaignCockpitSummaryBuilder::class);
});
