<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignFeatureProfileData extends Data
{
    /**
     * @param  array<string, bool>  $features
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $name = 'baseline',
        public readonly array $features = [],
        public readonly array $metadata = [],
    ) {}
}
