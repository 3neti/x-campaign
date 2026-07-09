<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignCockpitApiResponses;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitApiResponsePresenter;

it('binds cockpit api response presentation to the read-only presenter', function () {
    expect(app(PresentsCampaignCockpitApiResponses::class))
        ->toBeInstanceOf(CampaignCockpitApiResponsePresenter::class);
});
