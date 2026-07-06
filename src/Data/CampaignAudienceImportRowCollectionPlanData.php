<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportRowCollectionPlanData extends Data
{
    /**
     * @param  array<int, CampaignRecipientImportRowData>  $rows
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly string $status = 'empty',
        public readonly int $totalRows = 0,
        public readonly int $validRows = 0,
        public readonly int $invalidRows = 0,
        public readonly array $rows = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
