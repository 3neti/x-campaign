<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignWorksheetImportData;

interface CampaignWorksheetImportRepository
{
    public function stage(CampaignWorksheetImportData $import, string $ownerType, string $ownerId): CampaignWorksheetImportData;

    public function findForOwner(string $worksheetReference, string $importReference, string $ownerType, string $ownerId): ?CampaignWorksheetImportData;

    /** @return array<int, CampaignWorksheetImportData> */
    public function forOwner(string $worksheetReference, string $ownerType, string $ownerId): array;

    public function apply(string $worksheetReference, string $importReference, string $ownerType, string $ownerId): CampaignWorksheetImportData;

    /**
     * @param  array<string, string>  $mapping
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function replaceUnappliedRows(
        string $worksheetReference,
        string $importReference,
        string $ownerType,
        string $ownerId,
        array $mapping,
        array $rows,
    ): CampaignWorksheetImportData;

    public function discard(
        string $worksheetReference,
        string $importReference,
        string $ownerType,
        string $ownerId,
    ): CampaignWorksheetImportData;
}
