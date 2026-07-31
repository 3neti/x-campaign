<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPlanningWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPlanningWorkspace;

it('binds the campaign planning workspace to the repository-backed baseline', function () {
    expect(app(CampaignPlanningWorkspace::class))->toBeInstanceOf(RepositoryBackedCampaignPlanningWorkspace::class);
});
