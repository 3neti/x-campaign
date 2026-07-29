<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignWorksheetData;
use LBHurtado\XCampaign\Data\CampaignWorksheetRowData;
use LBHurtado\XCampaign\Data\CampaignWorksheetSummaryData;

interface CampaignWorksheetRepository
{
    public function put(CampaignWorksheetData $worksheet): CampaignWorksheetData;

    public function findForOwner(string $reference, string $ownerType, string $ownerId): ?CampaignWorksheetData;

    public function appendRow(
        string $reference,
        string $ownerType,
        string $ownerId,
        CampaignWorksheetRowData $row,
    ): CampaignWorksheetData;

    /**
     * @param  array<int, CampaignWorksheetRowData>  $rows
     */
    public function appendRows(
        string $reference,
        string $ownerType,
        string $ownerId,
        array $rows,
    ): CampaignWorksheetData;

    public function freeze(string $reference, string $ownerType, string $ownerId): CampaignWorksheetData;

    /**
     * @return array<int, CampaignWorksheetSummaryData>
     */
    public function summariesForOwner(string $ownerType, string $ownerId): array;
}
