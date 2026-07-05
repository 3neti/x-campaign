<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $campaignId = null,
        public readonly string $name = '',
        public readonly string $status = 'draft',
        public readonly array $metadata = [],
    ) {}
}
