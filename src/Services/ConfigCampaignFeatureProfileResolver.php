<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use LBHurtado\XCampaign\Contracts\CampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Data\CampaignFeatureProfileData;

class ConfigCampaignFeatureProfileResolver implements CampaignFeatureProfileResolver
{
    public function resolve(?string $profile = null): CampaignFeatureProfileData
    {
        $name = is_string($profile) && trim($profile) !== ''
            ? trim($profile)
            : (string) config('x-campaign.feature_profile.default', 'baseline');

        return new CampaignFeatureProfileData(
            name: $name,
            features: [],
            metadata: [
                'source' => 'config',
                'owns_execution' => false,
                'owns_notification_transport' => false,
            ],
        );
    }
}
