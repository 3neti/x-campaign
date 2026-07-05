<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignDeliveryData extends Data
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $campaignId = null,
        public readonly ?string $executionId = null,
        public readonly ?string $recipientId = null,
        public readonly string $channel = 'unknown',
        public readonly string $status = 'planned',
        public readonly array $payload = [],
        public readonly array $metadata = [],
    ) {}
}
