<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignHostMutationAuthorizationChecklistData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $gates
     * @param  array<int, string>  $blockedOperations
     * @param  array<int, string>  $hostResponsibilities
     * @param  array<int, string>  $packageResponsibilities
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $status,
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $operatorId,
        public readonly array $gates = [],
        public readonly array $blockedOperations = [],
        public readonly array $hostResponsibilities = [],
        public readonly array $packageResponsibilities = [],
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
