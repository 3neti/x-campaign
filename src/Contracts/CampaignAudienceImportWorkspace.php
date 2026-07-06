<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanningInputData;

interface CampaignAudienceImportWorkspace
{
    public function plan(string $planningKey, CampaignAudienceImportPlanningInputData $input): CampaignAudienceImportPlanData;
}

