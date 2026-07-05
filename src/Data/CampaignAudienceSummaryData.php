<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceSummaryData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $audienceId = null,
        public readonly string $name = '',
        public readonly string $status = 'draft',
        public readonly int $recipientCount = 0,
        public readonly array $metadata = [],
    ) {}
}

