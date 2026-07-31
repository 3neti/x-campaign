<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

interface MapsCampaignQueuedPayloadsToDeliveryHandoffs
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignDeliveryHandoffWorkspaceInputData;
}
