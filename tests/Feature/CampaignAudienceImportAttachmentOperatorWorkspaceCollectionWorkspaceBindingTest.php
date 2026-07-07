<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;

it('binds attachment operator workspace collection workspace contracts to the repository-backed in-memory baseline', function () {
    expect(app(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace::class))->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace::class);
});
