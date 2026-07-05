<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

interface CreatesCampaignPlans
{
    public function handle(CampaignPlanningInputData $input): CampaignPlanData;
}
