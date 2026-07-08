<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignCockpitWorkspace;

it('defines a cockpit workspace contract for read-only package composition', function () {
    expect(interface_exists(CampaignCockpitWorkspace::class))->toBeTrue()
        ->and(method_exists(CampaignCockpitWorkspace::class, 'summary'))->toBeTrue()
        ->and(method_exists(CampaignCockpitWorkspace::class, 'effects'))->toBeTrue();
});
