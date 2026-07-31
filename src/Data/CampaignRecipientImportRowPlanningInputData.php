<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignRecipientImportRowPlanningInputData extends Data
{
    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly int $rowNumber = 1,
        public readonly array $row = [],
        public readonly array $metadata = [],
    ) {}
}
