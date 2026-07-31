<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData;

interface CampaignAudienceImportRecipientAttachmentMutationWorkspace
{
    public function attach(
        string $planningKey,
        CampaignAudienceImportApprovalWorkspaceInputData $approvalInput,
        CampaignAudienceImportRecipientAttachmentMutationDecisionInputData $mutationInput,
    ): CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData;
}
