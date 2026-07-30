<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class CampaignWorksheetData extends Data
{
    /**
     * @param  array<int, CampaignWorksheetRowData>  $rows
     * @param  array<int, string>  $deliveryPlan
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $reference,
        public readonly string $ownerType,
        public readonly string $ownerId,
        public readonly string $profile,
        public readonly string $name,
        public readonly string $currency = 'PHP',
        public readonly string $status = 'draft',
        public readonly ?string $payCodeTemplateReference = null,
        public readonly string $fulfillmentMode = 'pay_code_distribution',
        public readonly array $deliveryPlan = [],
        public readonly array $rows = [],
        public readonly array $metadata = [],
        public readonly ?string $rowsHash = null,
        public readonly array $instructionBlueprint = [],
        public readonly ?string $instructionBlueprintHash = null,
        public readonly ?string $instructionBlueprintSchema = null,
        public readonly int $instructionBlueprintRevision = 0,
        public readonly ?string $manifestHash = null,
        public readonly ?CarbonImmutable $frozenAt = null,
    ) {}
}
