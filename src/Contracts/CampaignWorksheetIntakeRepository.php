<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignWorksheetData;
use LBHurtado\XCampaign\Data\CampaignWorksheetIntakeData;

interface CampaignWorksheetIntakeRepository
{
    public function stage(CampaignWorksheetIntakeData $intake): CampaignWorksheetIntakeData;

    public function findForOwner(string $reference, string $ownerType, string $ownerId): ?CampaignWorksheetIntakeData;

    public function activeForOwner(string $ownerType, string $ownerId): ?CampaignWorksheetIntakeData;

    public function duplicateForOwner(string $contentHash, string $ownerType, string $ownerId): ?CampaignWorksheetIntakeData;

    /**
     * @param  array<string, string>  $mapping
     * @param  array<string, mixed>  $suggestion
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function replaceReview(
        string $reference,
        string $ownerType,
        string $ownerId,
        array $mapping,
        array $suggestion,
        array $rows,
    ): CampaignWorksheetIntakeData;

    /**
     * @param  array<int, int>  $includedSourceRows
     */
    public function convert(
        string $reference,
        string $ownerType,
        string $ownerId,
        CampaignWorksheetData $worksheet,
        array $includedSourceRows,
    ): CampaignWorksheetData;

    public function discard(string $reference, string $ownerType, string $ownerId): void;
}
