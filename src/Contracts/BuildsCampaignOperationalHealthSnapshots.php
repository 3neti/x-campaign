<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalSignalData;

interface BuildsCampaignOperationalHealthSnapshots
{
    public function build(CampaignOperationalSignalData $signal): CampaignOperationalHealthSnapshotData;
}
