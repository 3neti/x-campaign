<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignProductionReadinessWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignProductionReadinessWorkspace;

it('binds production readiness to the repository-backed readiness workspace', function () {
    expect(app(CampaignProductionReadinessWorkspace::class))
        ->toBeInstanceOf(RepositoryBackedCampaignProductionReadinessWorkspace::class);
});
