<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignDeliveryResultData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $status = 'planned',
        public readonly ?string $deliveryId = null,
        public readonly ?string $providerReference = null,
        public readonly bool $retryable = false,
        public readonly array $metadata = [],
    ) {}
}
