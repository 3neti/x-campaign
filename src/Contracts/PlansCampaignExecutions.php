<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

interface PlansCampaignExecutions
{
    public function handle(CampaignPlanData $plan, CampaignExecutionPlanningInputData $input): CampaignPlanData;
}
