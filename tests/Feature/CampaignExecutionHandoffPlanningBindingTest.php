<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignExecutionHandoff;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionHandoffs;

it('binds execution handoff planning to the in-memory handoff planner', function () {
    expect(app(PlansCampaignExecutionHandoffs::class))->toBeInstanceOf(PlanCampaignExecutionHandoff::class);
});
