<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionBatches;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignBatchData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class PlanCampaignExecutionBatches implements PlansCampaignExecutionBatches
{
    public function handle(CampaignPlanData $plan, string $executionId, int $batchSize): CampaignPlanData
    {
        if ($batchSize <= 0) {
            throw new InvalidArgumentException('Campaign execution batch size must be greater than zero.');
        }

        $matched = false;

        $executions = array_map(function (CampaignExecutionPlanData $execution) use ($plan, $executionId, $batchSize, &$matched): CampaignExecutionPlanData {
            if ($execution->execution->id !== $executionId) {
                return $execution;
            }

            $matched = true;
            $recipientCount = $this->recipientCount($plan, $execution->execution->audienceId);

            return new CampaignExecutionPlanData(
                execution: $execution->execution,
                batches: $this->batches($executionId, $recipientCount, $batchSize),
                effects: [
                    ...$execution->effects,
                    ...$this->effects(),
                ],
                metadata: [
                    ...$execution->metadata,
                    'batch_size' => $batchSize,
                    'batch_count' => $recipientCount === 0 ? 0 : (int) ceil($recipientCount / $batchSize),
                ],
            );
        }, $plan->executions);

        if (! $matched) {
            throw new InvalidArgumentException("Unknown campaign execution plan [{$executionId}].");
        }

        return new CampaignPlanData(
            campaign: $plan->campaign,
            audiences: $plan->audiences,
            executions: $executions,
            effects: [
                ...$plan->effects,
                ...$this->effects(),
            ],
            metadata: [
                ...$plan->metadata,
                'batch_planning' => 'in-memory',
            ],
        );
    }

    /**
     * @return array<int, CampaignBatchData>
     */
    private function batches(string $executionId, int $recipientCount, int $batchSize): array
    {
        if ($recipientCount === 0) {
            return [];
        }

        $batches = [];
        $remaining = $recipientCount;
        $sequence = 1;

        while ($remaining > 0) {
            $count = min($batchSize, $remaining);

            $batches[] = new CampaignBatchData(
                id: $executionId.'-batch-'.$sequence,
                executionId: $executionId,
                sequence: $sequence,
                recipientCount: $count,
                status: 'planned',
                metadata: [
                    'source' => 'in-memory-batch-planning',
                ],
            );

            $remaining -= $count;
            $sequence++;
        }

        return $batches;
    }

    private function recipientCount(CampaignPlanData $plan, ?string $audienceId): int
    {
        foreach ($plan->audiences as $audience) {
            if ($audience instanceof CampaignAudiencePlanData && $audience->audience->id === $audienceId) {
                return count($audience->recipients);
            }
        }

        return 0;
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }
}
