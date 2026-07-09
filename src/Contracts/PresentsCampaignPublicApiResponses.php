<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiResponseData;

interface PresentsCampaignPublicApiResponses
{
    public function present(CampaignPublicApiDescriptorData $descriptor): CampaignPublicApiResponseData;
}
