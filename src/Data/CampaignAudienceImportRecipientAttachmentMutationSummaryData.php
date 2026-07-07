<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRecipientAttachmentMutationSummaryData extends Data
{
    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly string $status = 'blocked',
        public readonly string $decisionStatus = 'blocked',
        public readonly string $mutationStatus = 'blocked',
        public readonly int $totalRows = 0,
        public readonly int $attachableRows = 0,
        public readonly int $blockedRows = 0,
        public readonly int $attachedRows = 0,
        public readonly int $skippedRows = 0,
        public readonly int $beforeRecipientCount = 0,
        public readonly int $afterRecipientCount = 0,
        public readonly int $recipientDelta = 0,
        public readonly bool $readyForMutation = false,
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
