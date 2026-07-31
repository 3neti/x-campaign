<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRecipientAttachmentMutationDecisionInputData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $decision,
        public readonly ?string $decidedBy = null,
        public readonly ?string $reason = null,
        public readonly array $metadata = [],
    ) {}
}
