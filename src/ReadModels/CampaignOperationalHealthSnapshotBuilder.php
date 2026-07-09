<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperationalHealthSnapshots;
use LBHurtado\XCampaign\Data\CampaignOperationalHealthSnapshotData;
use LBHurtado\XCampaign\Data\CampaignOperationalSignalData;

class CampaignOperationalHealthSnapshotBuilder implements BuildsCampaignOperationalHealthSnapshots
{
    public function build(CampaignOperationalSignalData $signal): CampaignOperationalHealthSnapshotData
    {
        $blockers = $this->blockers($signal);

        return new CampaignOperationalHealthSnapshotData(
            status: $blockers === [] ? 'healthy' : 'attention_required',
            planningKey: $signal->planningKey,
            executionId: $signal->executionId,
            operatorId: $signal->operatorId,
            checks: [
                'cockpit' => $signal->cockpitSummary->status,
                'api_response' => $signal->apiResponse->status,
            ],
            indicators: [
                'blocker_count' => count($blockers),
                'action_count' => count($signal->cockpitSummary->actions),
            ],
            blockers: $blockers,
            metadata: [
                ...$signal->metadata,
                'source' => 'campaign-operational-health-snapshot-builder',
                'read_only' => true,
                'exports_metrics' => false,
                'sends_alerts' => false,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignOperationalSignalData $signal): array
    {
        $apiBlockers = $signal->apiResponse->data['blockers'] ?? [];

        if (! is_array($apiBlockers)) {
            $apiBlockers = [];
        }

        return array_values(array_unique([
            ...$signal->cockpitSummary->blockers,
            ...array_filter($apiBlockers, is_string(...)),
        ]));
    }
}
