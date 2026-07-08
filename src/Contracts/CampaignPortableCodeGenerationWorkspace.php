<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationWorkspaceResultData;

interface CampaignPortableCodeGenerationWorkspace
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function plan(
        string $planningKey,
        string $executionId,
        ?string $batchId = null,
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignPortableCodeGenerationWorkspaceResultData;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}

