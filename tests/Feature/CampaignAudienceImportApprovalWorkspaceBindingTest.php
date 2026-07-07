<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignAudienceImportApprovalWorkspace;

it('binds audience import approval workspace contracts to the repository-backed baseline', function () {
    expect(app(CampaignAudienceImportApprovalWorkspace::class))->toBeInstanceOf(CampaignAudienceImportApprovalWorkspace::class);
});
