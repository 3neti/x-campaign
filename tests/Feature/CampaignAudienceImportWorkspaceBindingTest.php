<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignAudienceImportWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportWorkspace;

it('binds the audience import workspace to the repository-backed non-parsing baseline', function () {
    expect(app(CampaignAudienceImportWorkspace::class))->toBeInstanceOf(RepositoryBackedCampaignAudienceImportWorkspace::class);
});

