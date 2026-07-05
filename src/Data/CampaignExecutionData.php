<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignExecutionData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $campaignId = null,
        public readonly ?string $audienceId = null,
        public readonly string $status = 'planned',
        public readonly ?string $scheduledAt = null,
        public readonly ?string $correlationId = null,
        public readonly array $metadata = [],
    ) {}
}
