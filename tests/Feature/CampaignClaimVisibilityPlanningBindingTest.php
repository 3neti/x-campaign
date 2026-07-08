<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignClaimVisibility;
use LBHurtado\XCampaign\Contracts\PlansCampaignClaimVisibilities;

it('binds claim visibility planning to the in-memory planner', function () {
    expect(app(PlansCampaignClaimVisibilities::class))->toBeInstanceOf(PlanCampaignClaimVisibility::class);
});

