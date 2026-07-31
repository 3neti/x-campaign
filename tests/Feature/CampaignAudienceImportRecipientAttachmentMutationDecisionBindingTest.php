<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportRecipientAttachmentMutations;

it('binds recipient attachment mutation decision contracts to the decision-only baseline', function () {
    expect(app(DecidesCampaignAudienceImportRecipientAttachmentMutations::class))->toBeInstanceOf(DecidesCampaignAudienceImportRecipientAttachmentMutations::class);
});
