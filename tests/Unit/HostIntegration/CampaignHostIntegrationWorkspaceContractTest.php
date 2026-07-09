<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignHostIntegrationWorkspace;

it('defines a host integration workspace contract', function () {
    expect(interface_exists(CampaignHostIntegrationWorkspace::class))->toBeTrue()
        ->and(method_exists(CampaignHostIntegrationWorkspace::class, 'manifest'))->toBeTrue()
        ->and(method_exists(CampaignHostIntegrationWorkspace::class, 'effects'))->toBeTrue();
});
