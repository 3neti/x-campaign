<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;

interface CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace
{
    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     * @param  array<string, mixed>  $metadata
     */
    public function overview(
        string $planningKey,
        array $results,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData;
}
