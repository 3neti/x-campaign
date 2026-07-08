<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignQueueDispatchData;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchResultData;

interface PlansCampaignQueueDispatches
{
    public function plan(CampaignQueueDispatchData $dispatch): CampaignQueueDispatchResultData;
}
