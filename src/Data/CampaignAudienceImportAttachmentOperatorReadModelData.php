<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportAttachmentOperatorReadModelData extends Data
{
    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $campaignId = null,
        public readonly ?string $audienceId = null,
        public readonly string $status = 'empty',
        public readonly int $totalImports = 0,
        public readonly int $attachedImports = 0,
        public readonly int $blockedImports = 0,
        public readonly int $deferredImports = 0,
        public readonly int $totalRows = 0,
        public readonly int $attachedRows = 0,
        public readonly int $blockedRows = 0,
        public readonly int $recipientDelta = 0,
        public readonly array $summaries = [],
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
