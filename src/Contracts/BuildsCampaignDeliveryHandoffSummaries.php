<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffSummaryData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceResultData;

interface BuildsCampaignDeliveryHandoffSummaries
{
    public function fromWorkspaceResult(CampaignDeliveryHandoffWorkspaceResultData $result): CampaignDeliveryHandoffSummaryData;
}

