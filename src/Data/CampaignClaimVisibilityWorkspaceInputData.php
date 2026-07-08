<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignClaimVisibilityWorkspaceInputData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $requestedBy = 'queue',
        public readonly ?string $correlationId = null,
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}

