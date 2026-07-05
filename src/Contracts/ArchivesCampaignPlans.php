<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;

interface ArchivesCampaignPlans
{
    public function handle(CampaignPlanData $plan, ?string $reason = null): CampaignPlanData;
}
