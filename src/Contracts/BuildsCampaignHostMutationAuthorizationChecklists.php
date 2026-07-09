<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignHostMutationAuthorizationChecklistData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

interface BuildsCampaignHostMutationAuthorizationChecklists
{
    public function build(CampaignXChangeIntegrationRequestData $request): CampaignHostMutationAuthorizationChecklistData;
}
