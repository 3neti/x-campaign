<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignBatchData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $executionId = null,
        public readonly int $sequence = 1,
        public readonly int $recipientCount = 0,
        public readonly string $status = 'planned',
        public readonly array $metadata = [],
    ) {}
}
