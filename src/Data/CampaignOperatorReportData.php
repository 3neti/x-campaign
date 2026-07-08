<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignOperatorReportData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $sections
     * @param  array<int, string>  $blockers
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $status,
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $reportType,
        public readonly string $format,
        public readonly string $title,
        public readonly array $sections = [],
        public readonly array $blockers = [],
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
