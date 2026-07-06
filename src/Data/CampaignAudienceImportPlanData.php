<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportPlanData extends Data
{
    /**
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignImportData $import,
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}

