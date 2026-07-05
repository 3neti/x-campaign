<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignExecutionPlanningInputData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $audienceId = null,
        public readonly ?string $scheduledAt = null,
        public readonly ?string $correlationId = null,
        public readonly array $metadata = [],
    ) {}
}
