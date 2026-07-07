<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRecipientAttachmentMutationDecisionData extends Data
{
    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly string $decision = 'attach',
        public readonly string $status = 'blocked',
        public readonly ?string $decidedBy = null,
        public readonly ?string $reason = null,
        public readonly bool $readyForMutation = false,
        public readonly int $attachableRows = 0,
        public readonly int $blockedRows = 0,
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}

