<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignExportHandoffWorkspaceInputData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $reportType = 'operator_summary',
        public readonly string $format = 'csv',
        public readonly string $destination = 'operator_download',
        public readonly ?string $correlationId = null,
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
