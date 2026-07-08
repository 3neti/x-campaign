<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExecutionHandoffData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;

interface PlansCampaignExecutionHandoffs
{
    public function plan(CampaignExecutionHandoffData $handoff): CampaignExecutionHandoffResultData;
}
