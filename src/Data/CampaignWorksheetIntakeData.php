<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignWorksheetIntakeData extends Data
{
    /**
     * @param  array<int, string>  $sourceHeaders
     * @param  array<string, string>  $mapping
     * @param  array<string, mixed>  $suggestion
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        public readonly ?string $reference,
        public readonly string $ownerType,
        public readonly string $ownerId,
        public readonly string $status,
        public readonly string $sourceName,
        public readonly string $sourceFormat,
        public readonly string $contentHash,
        public readonly int $rowCount,
        public readonly array $sourceHeaders,
        public readonly ?string $sourceSheet,
        public readonly array $mapping,
        public readonly array $suggestion,
        public readonly array $rows,
        public readonly ?string $convertedWorksheetReference = null,
        public readonly ?string $convertedAt = null,
    ) {}
}
