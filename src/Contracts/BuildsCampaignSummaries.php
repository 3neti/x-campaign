<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;

interface BuildsCampaignSummaries
{
    public function fromPlan(CampaignPlanData $plan): CampaignSummaryData;
}

