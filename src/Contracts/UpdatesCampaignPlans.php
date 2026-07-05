<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

interface UpdatesCampaignPlans
{
    public function handle(CampaignPlanData $plan, CampaignPlanningInputData $input): CampaignPlanData;
}
