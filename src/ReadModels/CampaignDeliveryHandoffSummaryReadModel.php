<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignDeliveryHandoffSummaries;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffSummaryData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceResultData;

class CampaignDeliveryHandoffSummaryReadModel implements BuildsCampaignDeliveryHandoffSummaries
{
    public function fromWorkspaceResult(CampaignDeliveryHandoffWorkspaceResultData $result): CampaignDeliveryHandoffSummaryData
    {
        return new CampaignDeliveryHandoffSummaryData(
            status: $result->status,
            planningKey: $result->planningKey,
            executionId: $result->executionId,
            channel: $result->channel,
            recipientCount: count($result->handoffResults),
            readyCount: $this->countByStatus($result, 'ready'),
            blockedCount: $this->countBlocked($result),
            ready: $result->status === 'ready' && $result->blockers === [],
            blockers: $this->blockers($result),
            effects: $result->effects->toArray(),
            metadata: [
                ...$result->metadata,
                'source' => 'delivery-handoff-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    private function countByStatus(CampaignDeliveryHandoffWorkspaceResultData $result, string $status): int
    {
        return count(array_filter(
            $result->handoffResults,
            fn (mixed $handoffResult): bool => $handoffResult instanceof CampaignDeliveryHandoffResultData
                && $handoffResult->status === $status,
        ));
    }

    private function countBlocked(CampaignDeliveryHandoffWorkspaceResultData $result): int
    {
        return count(array_filter(
            $result->handoffResults,
            fn (mixed $handoffResult): bool => $handoffResult instanceof CampaignDeliveryHandoffResultData
                && ($handoffResult->status === 'blocked' || $handoffResult->blockers !== []),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignDeliveryHandoffWorkspaceResultData $result): array
    {
        if ($result->blockers !== []) {
            return $result->blockers;
        }

        $blockers = [];

        foreach ($result->handoffResults as $handoffResult) {
            if ($handoffResult instanceof CampaignDeliveryHandoffResultData) {
                $blockers = [
                    ...$blockers,
                    ...$handoffResult->blockers,
                ];
            }
        }

        return array_values(array_unique($blockers));
    }
}

