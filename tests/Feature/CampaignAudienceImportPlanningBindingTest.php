<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImports;

it('binds audience import planning contracts to the non-parsing baseline', function () {
    expect(app(PlansCampaignAudienceImports::class))->toBeInstanceOf(PlansCampaignAudienceImports::class);
});

