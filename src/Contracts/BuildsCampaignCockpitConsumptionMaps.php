<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignCockpitConsumptionMapData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

interface BuildsCampaignCockpitConsumptionMaps
{
    public function build(CampaignXChangeIntegrationRequestData $request): CampaignCockpitConsumptionMapData;
}
