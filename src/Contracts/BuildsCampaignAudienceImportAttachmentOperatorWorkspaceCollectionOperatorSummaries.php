<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData;

interface BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function fromWorkspaceResult(
        CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData $result,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData;
}
