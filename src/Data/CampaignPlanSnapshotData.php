<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignPlanSnapshotData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly CampaignPlanData $plan,
        public readonly int $version = 1,
        public readonly ?string $checksum = null,
        public readonly array $metadata = [],
    ) {}
}
