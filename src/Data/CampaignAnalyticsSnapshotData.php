<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAnalyticsSnapshotData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $status,
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly int $recipientCount = 0,
        public readonly int $generatedCount = 0,
        public readonly int $deliveryReadyCount = 0,
        public readonly int $claimVisibleCount = 0,
        public readonly int $claimedCount = 0,
        public readonly array $blockers = [],
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
