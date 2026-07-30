<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignWorksheetImportData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $validRows
     * @param  array<int, array<string, mixed>>  $validationErrors
     * @param  array<string, string>  $mapping
     * @param  array<int, array<string, mixed>>  $stagedRows
     * @param  array<int, string>  $sourceHeaders
     */
    public function __construct(
        public readonly ?string $reference,
        public readonly string $worksheetReference,
        public readonly string $status,
        public readonly string $sourceFormat,
        public readonly string $contentHash,
        public readonly int $rowCount,
        public readonly array $validRows,
        public readonly array $validationErrors,
        public readonly array $mapping,
        public readonly ?string $appliedAt = null,
        public readonly array $stagedRows = [],
        public readonly array $sourceHeaders = [],
        public readonly ?string $sourceSheet = null,
    ) {}
}
