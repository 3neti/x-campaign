<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAnalyticsInputData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;

interface BuildsCampaignAnalyticsSnapshots
{
    public function build(CampaignAnalyticsInputData $input): CampaignAnalyticsSnapshotData;
}
