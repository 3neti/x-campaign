<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffResultData;

interface PlansCampaignDeliveryHandoffs
{
    public function plan(CampaignDeliveryHandoffData $handoff): CampaignDeliveryHandoffResultData;
}
