<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignProductionReadinessChecklistData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<int, string>  $requiredChecks
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $operatorId,
        public readonly CampaignOperationalReadinessData $operationalReadiness,
        public readonly array $requiredChecks = [
            'operational_readiness',
            'package_boundaries',
            'host_handoff',
        ],
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
