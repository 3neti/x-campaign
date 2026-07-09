<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalReadinessData;

interface PresentsCampaignOperationalReadiness
{
    public function present(CampaignOperationalHealthSnapshotData $snapshot): CampaignOperationalReadinessData;
}
