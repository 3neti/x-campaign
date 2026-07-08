<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignCockpitSummaryRequestData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly string $operatorId,
        public readonly CampaignSummaryData $campaignSummary,
        public readonly CampaignAnalyticsOperatorSummaryData $analyticsSummary,
        public readonly CampaignOperatorReportData $operatorReport,
        public readonly CampaignExportHandoffResultData $exportHandoff,
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
