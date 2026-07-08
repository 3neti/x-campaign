<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffSummaryData;

interface BuildsCampaignExecutionHandoffSummaries
{
    public function fromResult(CampaignExecutionHandoffResultData $result): CampaignExecutionHandoffSummaryData;
}
