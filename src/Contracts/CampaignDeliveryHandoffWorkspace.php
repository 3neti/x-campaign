<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceResultData;

interface CampaignDeliveryHandoffWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function plan(
        string $planningKey,
        string $executionId,
        string $channel = 'sms',
        string $requestedBy = 'system',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignDeliveryHandoffWorkspaceResultData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
