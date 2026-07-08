<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignCockpitWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignCockpitWorkspace;

it('binds cockpit workspace to the repository-backed read-only workspace', function () {
    expect(app(CampaignCockpitWorkspace::class))
        ->toBeInstanceOf(RepositoryBackedCampaignCockpitWorkspace::class);
});
