<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignHostIntegrationManifestData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $capabilities
     * @param  array<int, string>  $hostResponsibilities
     * @param  array<int, string>  $packageResponsibilities
     * @param  array<int, string>  $warnings
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $status,
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $operatorId,
        public readonly array $capabilities = [],
        public readonly array $hostResponsibilities = [],
        public readonly array $packageResponsibilities = [],
        public readonly array $warnings = [],
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
