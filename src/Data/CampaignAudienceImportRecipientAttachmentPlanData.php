<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRecipientAttachmentPlanData extends Data
{
    /**
     * @param  array<int, CampaignRecipientPlanningInputData>  $recipients
     * @param  array<int, int>  $attachableRowNumbers
     * @param  array<int, int>  $blockedRowNumbers
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly string $status = 'blocked',
        public readonly int $totalRows = 0,
        public readonly int $attachableRows = 0,
        public readonly int $blockedRows = 0,
        public readonly array $recipients = [],
        public readonly array $attachableRowNumbers = [],
        public readonly array $blockedRowNumbers = [],
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}

