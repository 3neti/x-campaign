<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;

interface RemovesRecipientsFromCampaignAudiencePlans
{
    public function handle(CampaignPlanData $plan, string $audienceId, string $recipientReference): CampaignPlanData;
}
