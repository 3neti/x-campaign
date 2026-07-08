<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAnalyticsOperatorSummaryData extends Data
{
    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $status,
        public readonly string $operatorPosture,
        public readonly int $recipientCount = 0,
        public readonly int $generatedCount = 0,
        public readonly int $deliveryReadyCount = 0,
        public readonly int $claimVisibleCount = 0,
        public readonly int $claimedCount = 0,
        public readonly int $blockerCount = 0,
        public readonly bool $ready = false,
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
