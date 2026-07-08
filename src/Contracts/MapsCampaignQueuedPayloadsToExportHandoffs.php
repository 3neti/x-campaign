<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExportHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

interface MapsCampaignQueuedPayloadsToExportHandoffs
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignExportHandoffWorkspaceInputData;
}
