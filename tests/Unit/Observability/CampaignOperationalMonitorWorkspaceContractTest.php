<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignOperationalMonitorWorkspace;

it('defines an operational monitor workspace contract', function () {
    expect(interface_exists(CampaignOperationalMonitorWorkspace::class))->toBeTrue()
        ->and(method_exists(CampaignOperationalMonitorWorkspace::class, 'snapshot'))->toBeTrue()
        ->and(method_exists(CampaignOperationalMonitorWorkspace::class, 'effects'))->toBeTrue();
});
