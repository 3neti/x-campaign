<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignClaimVisibilitySummaries;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityResultData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilitySummaryData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceResultData;

class CampaignClaimVisibilitySummaryReadModel implements BuildsCampaignClaimVisibilitySummaries
{
    public function fromWorkspaceResult(CampaignClaimVisibilityWorkspaceResultData $result): CampaignClaimVisibilitySummaryData
    {
        return new CampaignClaimVisibilitySummaryData(
            status: $result->status,
            planningKey: $result->planningKey,
            executionId: $result->executionId,
            recipientCount: count($result->visibilityResults),
            visibleCount: $this->countByStatus($result, 'visible'),
            blockedCount: $this->countBlocked($result),
            claimedCount: $this->countByClaimStatus($result, 'claimed'),
            unclaimedCount: $this->countByClaimStatus($result, 'unclaimed'),
            visible: $result->status === 'visible' && $result->blockers === [],
            blockers: $this->blockers($result),
            effects: $result->effects->toArray(),
            metadata: [
                ...$result->metadata,
                'source' => 'claim-visibility-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    private function countByStatus(CampaignClaimVisibilityWorkspaceResultData $result, string $status): int
    {
        return count(array_filter(
            $result->visibilityResults,
            fn (mixed $visibilityResult): bool => $visibilityResult instanceof CampaignClaimVisibilityResultData
                && $visibilityResult->status === $status,
        ));
    }

    private function countBlocked(CampaignClaimVisibilityWorkspaceResultData $result): int
    {
        return count(array_filter(
            $result->visibilityResults,
            fn (mixed $visibilityResult): bool => $visibilityResult instanceof CampaignClaimVisibilityResultData
                && ($visibilityResult->status === 'blocked' || $visibilityResult->blockers !== []),
        ));
    }

    private function countByClaimStatus(CampaignClaimVisibilityWorkspaceResultData $result, string $status): int
    {
        return count(array_filter(
            $result->visibilityResults,
            fn (mixed $visibilityResult): bool => $visibilityResult instanceof CampaignClaimVisibilityResultData
                && ($visibilityResult->visibility->claimStatus['status'] ?? null) === $status,
        ));
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignClaimVisibilityWorkspaceResultData $result): array
    {
        if ($result->blockers !== []) {
            return $result->blockers;
        }

        $blockers = [];

        foreach ($result->visibilityResults as $visibilityResult) {
            if ($visibilityResult instanceof CampaignClaimVisibilityResultData) {
                $blockers = [
                    ...$blockers,
                    ...$visibilityResult->blockers,
                ];
            }
        }

        return array_values(array_unique($blockers));
    }
}
