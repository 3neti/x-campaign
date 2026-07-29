<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignWorksheetSummaryData extends Data
{
    /**
     * @param  array<int, string>  $deliveryPlan
     */
    public function __construct(
        public readonly string $reference,
        public readonly string $profile,
        public readonly string $name,
        public readonly string $currency,
        public readonly string $status,
        public readonly ?string $payCodeTemplateReference,
        public readonly string $fulfillmentMode,
        public readonly array $deliveryPlan,
        public readonly int $beneficiaryCount,
        public readonly int $principalMinor,
        public readonly ?string $updatedAt,
    ) {}
}
