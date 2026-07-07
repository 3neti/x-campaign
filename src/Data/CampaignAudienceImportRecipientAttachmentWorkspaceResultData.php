<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRecipientAttachmentWorkspaceResultData extends Data
{
    /**
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignAudienceImportApprovalWorkspaceResultData $approval,
        public readonly CampaignAudienceImportRecipientAttachmentPlanData $attachment,
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}

