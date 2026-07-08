<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignQueueDispatchResultData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

interface DispatchesCampaignQueuedPlans
{
    public function dispatch(
        CampaignQueuedPlanPayloadData $payload,
        ?string $queue = null,
        ?string $connection = null,
    ): CampaignQueueDispatchResultData;
}
