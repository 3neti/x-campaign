<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;

interface PlansCampaignExecutionBatches
{
    public function handle(CampaignPlanData $plan, string $executionId, int $batchSize): CampaignPlanData;
}
