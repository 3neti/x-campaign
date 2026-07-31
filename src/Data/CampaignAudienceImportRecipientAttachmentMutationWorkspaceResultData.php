<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData extends Data
{
    /**
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspace,
        public readonly CampaignAudienceImportRecipientAttachmentMutationDecisionData $decision,
        public readonly CampaignAudienceImportRecipientAttachmentMutationResultData $mutation,
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
