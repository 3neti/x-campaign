<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPublicApiWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPublicApiWorkspace;

it('binds public api to the repository-backed workspace', function () {
    expect(app(CampaignPublicApiWorkspace::class))
        ->toBeInstanceOf(RepositoryBackedCampaignPublicApiWorkspace::class);
});
