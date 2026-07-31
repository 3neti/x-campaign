<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentPlanData;

interface PlansCampaignAudienceImportRecipientAttachments
{
    public function handle(CampaignAudienceImportApprovalWorkspaceResultData $workspaceResult): CampaignAudienceImportRecipientAttachmentPlanData;
}
