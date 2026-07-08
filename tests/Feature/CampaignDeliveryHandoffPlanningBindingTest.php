<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignDeliveryHandoff;
use LBHurtado\XCampaign\Contracts\PlansCampaignDeliveryHandoffs;

it('binds delivery handoff planning to the in-memory handoff planner', function () {
    expect(app(PlansCampaignDeliveryHandoffs::class))->toBeInstanceOf(PlanCampaignDeliveryHandoff::class);
});

