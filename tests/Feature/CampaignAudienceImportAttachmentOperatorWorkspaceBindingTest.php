<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace;

it('binds audience import attachment operator workspaces to repository-backed read-only implementations', function () {
    expect(app(CampaignAudienceImportAttachmentOperatorWorkspace::class))->toBeInstanceOf(RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace::class);
});
