<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignQueueDispatch;
use LBHurtado\XCampaign\Contracts\PlansCampaignQueueDispatches;

it('binds queue dispatch planning to the in-memory planning-only implementation', function () {
    $planner = app(PlansCampaignQueueDispatches::class);

    expect($planner)->toBeInstanceOf(PlanCampaignQueueDispatch::class);
});
