<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanningInputData;

interface PlansCampaignAudienceImportRowCollections
{
    public function handle(string $planningKey, CampaignAudienceImportRowCollectionPlanningInputData $input): CampaignAudienceImportRowCollectionPlanData;
}
