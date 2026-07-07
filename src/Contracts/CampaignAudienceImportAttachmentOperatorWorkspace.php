<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationSummaryData;

interface CampaignAudienceImportAttachmentOperatorWorkspace
{
    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     * @param  array<string, mixed>  $metadata
     */
    public function overview(
        string $planningKey,
        string $audienceId,
        array $summaries,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceResultData;
}
