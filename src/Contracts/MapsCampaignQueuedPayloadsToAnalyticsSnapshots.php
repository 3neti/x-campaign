<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAnalyticsWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

interface MapsCampaignQueuedPayloadsToAnalyticsSnapshots
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignAnalyticsWorkspaceInputData;
}
