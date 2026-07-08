<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignExecutionHandoffWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignExecutionHandoffWorkspace;

it('binds execution handoff workspace to the repository-backed workspace', function () {
    expect(app(CampaignExecutionHandoffWorkspace::class))->toBeInstanceOf(RepositoryBackedCampaignExecutionHandoffWorkspace::class);
});
