<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignExecutionPlanData extends Data
{
    /**
     * @param  array<int, CampaignBatchData>  $batches
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignExecutionData $execution,
        public readonly array $batches = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
