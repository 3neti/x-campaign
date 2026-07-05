<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

interface AddsAudiencesToCampaignPlans
{
    public function handle(CampaignPlanData $plan, CampaignAudiencePlanningInputData $input): CampaignPlanData;
}
