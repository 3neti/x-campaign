<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignClaimVisibilityData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityResultData;

interface PlansCampaignClaimVisibilities
{
    public function plan(CampaignClaimVisibilityData $visibility): CampaignClaimVisibilityResultData;
}

