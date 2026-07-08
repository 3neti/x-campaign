<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsOperatorSummaries;
use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;

class CampaignAnalyticsOperatorSummaryReadModel implements BuildsCampaignAnalyticsOperatorSummaries
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function fromSnapshot(CampaignAnalyticsSnapshotData $snapshot, array $metadata = []): CampaignAnalyticsOperatorSummaryData
    {
        return new CampaignAnalyticsOperatorSummaryData(
            planningKey: $snapshot->planningKey,
            executionId: $snapshot->executionId,
            status: $snapshot->status,
            operatorPosture: $this->operatorPosture($snapshot),
            recipientCount: $snapshot->recipientCount,
            generatedCount: $snapshot->generatedCount,
            deliveryReadyCount: $snapshot->deliveryReadyCount,
            claimVisibleCount: $snapshot->claimVisibleCount,
            claimedCount: $snapshot->claimedCount,
            blockerCount: count($snapshot->blockers),
            ready: $snapshot->status === 'ready' && $snapshot->blockers === [],
            blockers: $snapshot->blockers,
            effects: [
                'analytics_operator_summary' => true,
                'read_only' => true,
                'persists' => false,
                'uses_database' => false,
                'queues_jobs' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$snapshot->metadata,
                ...$metadata,
                'source' => 'campaign-analytics-operator-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    private function operatorPosture(CampaignAnalyticsSnapshotData $snapshot): string
    {
        if ($snapshot->status === 'ready' && $snapshot->blockers === []) {
            return 'ready_for_review';
        }

        return 'attention_required';
    }
}
