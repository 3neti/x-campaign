<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;

interface BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections
{
    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     * @param  array<string, mixed>  $metadata
     */
    public function fromWorkspaceResults(
        string $planningKey,
        array $results,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData;
}
