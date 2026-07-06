<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;

interface BuildsCampaignAudienceImportReviewSummaries
{
    public function fromCollectionPlan(CampaignAudienceImportRowCollectionPlanData $plan): CampaignAudienceImportReviewSummaryData;
}
