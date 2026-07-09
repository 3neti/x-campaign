<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostIntegrationManifests;
use LBHurtado\XCampaign\ReadModels\CampaignHostIntegrationManifestBuilder;

it('binds the host integration manifest builder contract', function () {
    expect(app(BuildsCampaignHostIntegrationManifests::class))
        ->toBeInstanceOf(CampaignHostIntegrationManifestBuilder::class);
});
