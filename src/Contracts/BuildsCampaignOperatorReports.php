<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportRequestData;

interface BuildsCampaignOperatorReports
{
    public function build(CampaignOperatorReportRequestData $request): CampaignOperatorReportData;
}
