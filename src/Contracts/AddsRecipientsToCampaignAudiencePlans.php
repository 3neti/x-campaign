<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

interface AddsRecipientsToCampaignAudiencePlans
{
    public function handle(CampaignPlanData $plan, string $audienceId, CampaignRecipientPlanningInputData $input): CampaignPlanData;
}
