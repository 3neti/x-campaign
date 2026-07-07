<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceResultData;

interface CampaignAudienceImportApprovalWorkspace
{
    public function decide(string $planningKey, CampaignAudienceImportApprovalWorkspaceInputData $input): CampaignAudienceImportApprovalWorkspaceResultData;
}
