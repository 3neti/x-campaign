<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignPublicApiResponses;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiResponsePresenter;

it('binds the public api response presenter contract', function () {
    expect(app(PresentsCampaignPublicApiResponses::class))
        ->toBeInstanceOf(CampaignPublicApiResponsePresenter::class);
});
