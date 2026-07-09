<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\PresentsCampaignOperationalReadiness;
use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalReadinessData;

class CampaignOperationalReadinessPresenter implements PresentsCampaignOperationalReadiness
{
    public function present(CampaignOperationalHealthSnapshotData $snapshot): CampaignOperationalReadinessData
    {
        return new CampaignOperationalReadinessData(
            status: $snapshot->status === 'healthy' ? 'ready' : $snapshot->status,
            summary: [
                'planning_key' => $snapshot->planningKey,
                'execution_id' => $snapshot->executionId,
                'operator_id' => $snapshot->operatorId,
                'health_status' => $snapshot->status,
                'checks' => $snapshot->checks,
                'indicators' => $snapshot->indicators,
                'blockers' => $snapshot->blockers,
            ],
            meta: [
                ...$snapshot->metadata,
                'source' => 'campaign-operational-readiness-presenter',
                'read_only' => true,
                'exports_metrics' => false,
                'sends_alerts' => false,
                'writes_journal' => false,
            ],
        );
    }
}
