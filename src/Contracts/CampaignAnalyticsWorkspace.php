<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;

interface CampaignAnalyticsWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function snapshot(
        string $planningKey,
        string $executionId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignAnalyticsSnapshotData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
