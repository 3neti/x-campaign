<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignRecipientPlanningInputData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $mobile = null,
        public readonly ?string $email = null,
        public readonly ?string $address = null,
        public readonly ?string $externalReference = null,
        public readonly array $metadata = [],
    ) {}
}
