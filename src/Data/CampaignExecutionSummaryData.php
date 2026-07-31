<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignExecutionSummaryData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $executionId = null,
        public readonly ?string $audienceId = null,
        public readonly string $status = 'planned',
        public readonly ?string $scheduledAt = null,
        public readonly int $batchCount = 0,
        public readonly int $plannedRecipientCount = 0,
        public readonly array $metadata = [],
    ) {}
}
