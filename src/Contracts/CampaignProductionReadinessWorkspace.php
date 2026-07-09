<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;

interface CampaignProductionReadinessWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function assess(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignProductionReadinessAssessmentData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
