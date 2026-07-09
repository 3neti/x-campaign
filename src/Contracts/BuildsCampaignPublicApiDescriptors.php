<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiRequestData;

interface BuildsCampaignPublicApiDescriptors
{
    public function build(CampaignPublicApiRequestData $request): CampaignPublicApiDescriptorData;
}
