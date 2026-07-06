<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRowCollectionPlanningInputData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly int $startingRowNumber = 1,
        public readonly array $rows = [],
        public readonly array $metadata = [],
    ) {}
}
