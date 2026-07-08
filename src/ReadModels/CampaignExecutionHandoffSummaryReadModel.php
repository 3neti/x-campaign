<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignExecutionHandoffSummaries;
use LBHurtado\XCampaign\Data\CampaignBatchData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffSummaryData;

class CampaignExecutionHandoffSummaryReadModel implements BuildsCampaignExecutionHandoffSummaries
{
    public function fromResult(CampaignExecutionHandoffResultData $result): CampaignExecutionHandoffSummaryData
    {
        $handoff = $result->handoff;
        $executionPlan = $handoff->executionPlan;
        $execution = $executionPlan->execution;

        return new CampaignExecutionHandoffSummaryData(
            status: $result->status,
            handoffId: $result->handoffId,
            planningKey: $handoff->planningKey,
            executionId: $execution->id,
            batchCount: count($executionPlan->batches),
            recipientCount: $this->recipientCount($result),
            ready: $result->status === 'ready' && $result->blockers === [],
            blockers: $result->blockers,
            effects: $result->effects->toArray(),
            metadata: [
                ...$result->metadata,
                'source' => 'execution-handoff-summary-read-model',
                'campaign_id' => $execution->campaignId,
                'audience_id' => $execution->audienceId,
                'read_only' => true,
            ],
        );
    }

    private function recipientCount(CampaignExecutionHandoffResultData $result): int
    {
        return array_reduce(
            $result->handoff->executionPlan->batches,
            fn (int $total, mixed $batch): int => $total + ($batch instanceof CampaignBatchData ? $batch->recipientCount : 0),
            0,
        );
    }
}
