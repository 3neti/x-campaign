<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignWorksheetRowData extends Data
{
    /**
     * @param  array<string, mixed>  $beneficiary
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $reference,
        public readonly int $ordinal,
        public readonly array $beneficiary,
        public readonly int $amountMinor,
        public readonly string $currency = 'PHP',
        public readonly ?string $deliveryPreference = null,
        public readonly string $status = 'draft',
        public readonly array $metadata = [],
    ) {}
}
