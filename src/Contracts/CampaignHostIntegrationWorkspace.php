<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;

interface CampaignHostIntegrationWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function manifest(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignHostIntegrationManifestData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
