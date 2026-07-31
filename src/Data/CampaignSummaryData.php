<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignSummaryData extends Data
{
    /**
     * @param  array<int, CampaignAudienceSummaryData>  $audiences
     * @param  array<int, CampaignExecutionSummaryData>  $executions
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $campaignId = null,
        public readonly string $name = '',
        public readonly string $status = 'draft',
        public readonly string $featureProfile = 'baseline',
        public readonly ?string $owner = null,
        public readonly ?string $issuer = null,
        public readonly ?string $scheduledAt = null,
        public readonly int $audienceCount = 0,
        public readonly int $recipientCount = 0,
        public readonly int $executionCount = 0,
        public readonly int $batchCount = 0,
        public readonly int $plannedRecipientCount = 0,
        public readonly array $audiences = [],
        public readonly array $executions = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
