<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignCockpitApiResponseData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;

interface PresentsCampaignCockpitApiResponses
{
    public function present(CampaignCockpitSummaryData $summary): CampaignCockpitApiResponseData;
}
