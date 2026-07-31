<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;

interface CampaignAudienceImportRecipientAttachmentWorkspace
{
    public function plan(string $planningKey, CampaignAudienceImportApprovalWorkspaceInputData $input): CampaignAudienceImportRecipientAttachmentWorkspaceResultData;
}
