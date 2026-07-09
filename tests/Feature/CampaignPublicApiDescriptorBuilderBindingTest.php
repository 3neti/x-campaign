<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiDescriptors;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiDescriptorBuilder;

it('binds the public api descriptor builder contract', function () {
    expect(app(BuildsCampaignPublicApiDescriptors::class))
        ->toBeInstanceOf(CampaignPublicApiDescriptorBuilder::class);
});
