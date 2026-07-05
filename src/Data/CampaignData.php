<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly string $featureProfile = 'baseline',
        public readonly ?string $owner = null,
        public readonly ?string $issuer = null,
        public readonly string $status = 'draft',
        public readonly ?string $scheduledAt = null,
        public readonly array $metadata = [],
    ) {}
}
