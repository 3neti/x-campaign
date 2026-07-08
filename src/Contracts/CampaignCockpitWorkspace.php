<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;

interface CampaignCockpitWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function summary(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignCockpitSummaryData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
