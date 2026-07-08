<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignClaimVisibilityData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $claimStatus
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly CampaignExecutionData $execution,
        public readonly CampaignRecipientData $recipient,
        public readonly CampaignPortableCodeGenerationResultData $generationResult,
        public readonly array $claimStatus = [],
        public readonly string $requestedBy = 'system',
        public readonly ?string $correlationId = null,
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}

