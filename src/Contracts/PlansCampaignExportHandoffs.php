<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExportHandoffRequestData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffResultData;

interface PlansCampaignExportHandoffs
{
    public function plan(CampaignExportHandoffRequestData $request): CampaignExportHandoffResultData;
}
