<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;

interface CampaignOperationalMonitorWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function snapshot(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignOperationalHealthSnapshotData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
