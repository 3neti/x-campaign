<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignHostIntegrationWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignHostIntegrationWorkspace;

it('binds host integration to the repository-backed workspace', function () {
    expect(app(CampaignHostIntegrationWorkspace::class))
        ->toBeInstanceOf(RepositoryBackedCampaignHostIntegrationWorkspace::class);
});
