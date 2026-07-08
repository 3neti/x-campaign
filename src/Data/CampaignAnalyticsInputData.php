<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAnalyticsInputData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly CampaignSummaryData $campaignSummary,
        public readonly ?CampaignPortableCodeGenerationSummaryData $generationSummary = null,
        public readonly ?CampaignDeliveryHandoffSummaryData $deliverySummary = null,
        public readonly ?CampaignClaimVisibilitySummaryData $claimVisibilitySummary = null,
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
