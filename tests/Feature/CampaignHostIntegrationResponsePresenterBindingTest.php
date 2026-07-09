<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignHostIntegrationResponses;
use LBHurtado\XCampaign\ReadModels\CampaignHostIntegrationResponsePresenter;

it('binds the host integration response presenter contract', function () {
    expect(app(PresentsCampaignHostIntegrationResponses::class))
        ->toBeInstanceOf(CampaignHostIntegrationResponsePresenter::class);
});
