<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;

interface DecidesCampaignAudienceImportApprovals
{
    public function handle(
        CampaignAudienceImportReviewSummaryData $summary,
        CampaignAudienceImportApprovalDecisionInputData $input,
    ): CampaignAudienceImportApprovalDecisionData;
}
