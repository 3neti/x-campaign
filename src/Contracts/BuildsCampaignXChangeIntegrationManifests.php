<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

interface BuildsCampaignXChangeIntegrationManifests
{
    public function build(CampaignXChangeIntegrationRequestData $request): CampaignXChangeIntegrationManifestData;
}
