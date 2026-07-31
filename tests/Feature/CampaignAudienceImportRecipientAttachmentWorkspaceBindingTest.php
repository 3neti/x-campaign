<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentWorkspace;

it('binds audience import recipient attachment workspace contracts to the repository-backed baseline', function () {
    expect(app(CampaignAudienceImportRecipientAttachmentWorkspace::class))->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentWorkspace::class);
});
