<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;

interface CampaignPublicApiWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function descriptor(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $apiVersion = 'v1',
        array $metadata = [],
    ): CampaignPublicApiDescriptorData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
