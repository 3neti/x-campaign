<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignProductionReleases;
use LBHurtado\XCampaign\ReadModels\CampaignProductionReleasePresenter;

it('binds the production release presenter contract', function () {
    expect(app(PresentsCampaignProductionReleases::class))
        ->toBeInstanceOf(CampaignProductionReleasePresenter::class);
});
