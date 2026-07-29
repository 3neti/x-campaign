<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignWorksheetData;

interface CampaignWorksheetRepository
{
    public function put(CampaignWorksheetData $worksheet): CampaignWorksheetData;

    public function findForOwner(string $reference, string $ownerType, string $ownerId): ?CampaignWorksheetData;
}
