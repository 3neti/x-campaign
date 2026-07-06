<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportReviewSummaries;

it('binds audience import review summary read models to the read-only baseline', function () {
    expect(app(BuildsCampaignAudienceImportReviewSummaries::class))->toBeInstanceOf(BuildsCampaignAudienceImportReviewSummaries::class);
});
