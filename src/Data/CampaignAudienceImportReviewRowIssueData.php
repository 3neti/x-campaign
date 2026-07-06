<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportReviewRowIssueData extends Data
{
    /**
     * @param  array<string, string>  $errors
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly int $rowNumber,
        public readonly ?string $externalReference = null,
        public readonly array $errors = [],
        public readonly array $metadata = [],
    ) {}
}
