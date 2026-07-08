<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryRequestData;

interface BuildsCampaignCockpitSummaries
{
    public function build(CampaignCockpitSummaryRequestData $request): CampaignCockpitSummaryData;
}
