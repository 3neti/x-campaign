<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

interface MapsCampaignQueuedPayloadsToClaimVisibilities
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignClaimVisibilityWorkspaceInputData;
}
