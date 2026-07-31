<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignClaimVisibilitySummaryData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceResultData;

interface BuildsCampaignClaimVisibilitySummaries
{
    public function fromWorkspaceResult(CampaignClaimVisibilityWorkspaceResultData $result): CampaignClaimVisibilitySummaryData;
}
