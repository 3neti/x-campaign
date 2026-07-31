<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;

interface DecidesCampaignAudienceImportRecipientAttachmentMutations
{
    public function handle(
        CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspaceResult,
        CampaignAudienceImportRecipientAttachmentMutationDecisionInputData $input,
    ): CampaignAudienceImportRecipientAttachmentMutationDecisionData;
}
