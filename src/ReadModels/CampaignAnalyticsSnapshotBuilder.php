<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsSnapshots;
use LBHurtado\XCampaign\Data\CampaignAnalyticsInputData;
use LBHurtado\XCampaign\Data\CampaignAnalyticsSnapshotData;

class CampaignAnalyticsSnapshotBuilder implements BuildsCampaignAnalyticsSnapshots
{
    public function build(CampaignAnalyticsInputData $input): CampaignAnalyticsSnapshotData
    {
        $blockers = $this->blockers($input);

        return new CampaignAnalyticsSnapshotData(
            status: $blockers === [] ? 'ready' : 'attention_required',
            planningKey: $input->planningKey,
            executionId: $input->executionId,
            recipientCount: $input->campaignSummary->recipientCount,
            generatedCount: $input->generationSummary?->plannedCount ?? 0,
            deliveryReadyCount: $input->deliverySummary?->readyCount ?? 0,
            claimVisibleCount: $input->claimVisibilitySummary?->visibleCount ?? 0,
            claimedCount: $input->claimVisibilitySummary?->claimedCount ?? 0,
            blockers: $blockers,
            metadata: [
                ...$input->metadata,
                'source' => 'analytics-snapshot-builder',
                'read_only' => true,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignAnalyticsInputData $input): array
    {
        return array_values(array_unique(array_filter([
            ...($input->generationSummary?->blockers ?? []),
            ...($input->deliverySummary?->blockers ?? []),
            ...($input->claimVisibilitySummary?->blockers ?? []),
        ], fn (mixed $blocker): bool => is_string($blocker) && $blocker !== '')));
    }
}
