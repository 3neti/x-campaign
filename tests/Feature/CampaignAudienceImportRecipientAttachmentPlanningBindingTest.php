<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRecipientAttachments;

it('binds audience import recipient attachment planning contracts to the planning-only baseline', function () {
    expect(app(PlansCampaignAudienceImportRecipientAttachments::class))->toBeInstanceOf(PlansCampaignAudienceImportRecipientAttachments::class);
});
