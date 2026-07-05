<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignPlanningInputData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly ?string $featureProfile = null,
        public readonly ?string $owner = null,
        public readonly ?string $issuer = null,
        public readonly ?string $scheduledAt = null,
        public readonly array $metadata = [],
    ) {}
}
