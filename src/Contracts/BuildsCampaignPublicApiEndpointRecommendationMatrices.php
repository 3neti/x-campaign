<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPublicApiEndpointRecommendationMatrixData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

interface BuildsCampaignPublicApiEndpointRecommendationMatrices
{
    public function build(CampaignXChangeIntegrationRequestData $request): CampaignPublicApiEndpointRecommendationMatrixData;
}
