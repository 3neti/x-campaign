<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRowCollections;

it('binds audience import row collection planning to the non-importing baseline', function () {
    expect(app(PlansCampaignAudienceImportRowCollections::class))->toBeInstanceOf(PlansCampaignAudienceImportRowCollections::class);
});
