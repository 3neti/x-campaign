<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;

interface BuildsCampaignAnalyticsOperatorSummaries
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function fromSnapshot(CampaignAnalyticsSnapshotData $snapshot, array $metadata = []): CampaignAnalyticsOperatorSummaryData;
}
