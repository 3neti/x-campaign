<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanningInputData;

interface PlansCampaignAudienceImports
{
    public function handle(CampaignAudienceImportPlanningInputData $input): CampaignAudienceImportPlanData;
}

