<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExecutionHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

interface MapsCampaignQueuedPayloadsToExecutionHandoffs
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignExecutionHandoffWorkspaceInputData;
}
