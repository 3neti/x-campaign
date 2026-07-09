<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignProductionReadinessWorkspace;

it('defines a production readiness workspace contract', function () {
    expect(interface_exists(CampaignProductionReadinessWorkspace::class))->toBeTrue()
        ->and(method_exists(CampaignProductionReadinessWorkspace::class, 'assess'))->toBeTrue()
        ->and(method_exists(CampaignProductionReadinessWorkspace::class, 'effects'))->toBeTrue();
});
