<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;

interface CampaignExecutionHandoffWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function plan(
        string $planningKey,
        string $executionId,
        string $requestedBy = 'system',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignExecutionHandoffResultData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
