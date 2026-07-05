<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignDeliveryData;
use LBHurtado\XCampaign\Data\CampaignDeliveryResultData;

interface CampaignChannelDriver
{
    public function key(): string;

    public function plan(CampaignDeliveryData $delivery): CampaignDeliveryResultData;
}
