<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignExportHandoffs;
use LBHurtado\XCampaign\ReadModels\CampaignExportHandoffPlanner;

it('binds export handoff planning to the no-side-effect planner', function () {
    expect(app(PlansCampaignExportHandoffs::class))
        ->toBeInstanceOf(CampaignExportHandoffPlanner::class);
});
