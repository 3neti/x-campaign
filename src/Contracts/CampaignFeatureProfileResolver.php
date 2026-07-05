<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignFeatureProfileData;

interface CampaignFeatureProfileResolver
{
    public function resolve(?string $profile = null): CampaignFeatureProfileData;
}
