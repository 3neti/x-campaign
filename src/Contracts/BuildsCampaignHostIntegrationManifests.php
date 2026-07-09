<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationRequestData;

interface BuildsCampaignHostIntegrationManifests
{
    public function build(CampaignHostIntegrationRequestData $request): CampaignHostIntegrationManifestData;
}
