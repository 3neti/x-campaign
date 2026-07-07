<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;

interface AttachesCampaignAudienceImportRecipientsInMemory
{
    public function handle(
        string $planningKey,
        CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspaceResult,
        CampaignAudienceImportRecipientAttachmentMutationDecisionData $decision,
    ): CampaignAudienceImportRecipientAttachmentMutationResultData;
}

