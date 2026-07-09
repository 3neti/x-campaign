<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignOperationalReadiness;
use LBHurtado\XCampaign\ReadModels\CampaignOperationalReadinessPresenter;

it('binds operational readiness presentation to the read-only presenter', function () {
    expect(app(PresentsCampaignOperationalReadiness::class))
        ->toBeInstanceOf(CampaignOperationalReadinessPresenter::class);
});
