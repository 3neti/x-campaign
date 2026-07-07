<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorReadModelData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationSummaryData;

interface BuildsCampaignAudienceImportAttachmentOperatorReadModels
{
    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     * @param  array<string, mixed>  $metadata
     */
    public function fromMutationSummaries(
        ?string $campaignId,
        ?string $audienceId,
        array $summaries,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorReadModelData;
}
