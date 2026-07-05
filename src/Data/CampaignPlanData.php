<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignPlanData extends Data
{
    /**
     * @param  array<int, CampaignAudienceData>  $audiences
     * @param  array<int, CampaignExecutionData>  $executions
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignData $campaign,
        public readonly array $audiences = [],
        public readonly array $executions = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
