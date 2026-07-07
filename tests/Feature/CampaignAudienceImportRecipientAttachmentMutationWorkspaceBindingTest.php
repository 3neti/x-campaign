<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentMutationWorkspace;

it('binds recipient attachment mutation workspace contracts to the repository-backed in-memory baseline', function () {
    expect(app(CampaignAudienceImportRecipientAttachmentMutationWorkspace::class))->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentMutationWorkspace::class);
});

