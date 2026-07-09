<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationResponseData;

interface PresentsCampaignHostIntegrationResponses
{
    public function present(CampaignHostIntegrationManifestData $manifest): CampaignHostIntegrationResponseData;
}
