<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowPlanningInputData;

interface CampaignRecipientImportRowWorkspace
{
    public function plan(string $planningKey, CampaignRecipientImportRowPlanningInputData $input): CampaignRecipientImportRowData;
}
