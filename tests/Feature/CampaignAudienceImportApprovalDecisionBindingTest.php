<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportApprovals;

it('binds audience import approval decision contracts to the decision-only baseline', function () {
    expect(app(DecidesCampaignAudienceImportApprovals::class))->toBeInstanceOf(DecidesCampaignAudienceImportApprovals::class);
});
