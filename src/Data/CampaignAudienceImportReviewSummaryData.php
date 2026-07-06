<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportReviewSummaryData extends Data
{
    /**
     * @param  array<int, int>  $validRowNumbers
     * @param  array<int, int>  $invalidRowNumbers
     * @param  array<int, CampaignAudienceImportReviewRowIssueData>  $issues
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly string $status = 'empty',
        public readonly int $totalRows = 0,
        public readonly int $validRows = 0,
        public readonly int $invalidRows = 0,
        public readonly bool $readyForApproval = false,
        public readonly array $validRowNumbers = [],
        public readonly array $invalidRowNumbers = [],
        public readonly array $issues = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
