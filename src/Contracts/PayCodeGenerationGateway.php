<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

interface PayCodeGenerationGateway
{
    /**
     * @return array<string, mixed>
     */
    public function generate(CampaignExecutionData $execution, CampaignRecipientData $recipient): array;
}
