<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignOperationalMonitorWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignOperationalMonitorWorkspace;

it('binds operational monitoring to the repository-backed diagnostic workspace', function () {
    expect(app(CampaignOperationalMonitorWorkspace::class))
        ->toBeInstanceOf(RepositoryBackedCampaignOperationalMonitorWorkspace::class);
});
