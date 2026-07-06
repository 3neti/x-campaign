<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowPlanningInputData;

interface PlansCampaignRecipientImportRows
{
    public function handle(CampaignRecipientImportRowPlanningInputData $input): CampaignRecipientImportRowData;
}

