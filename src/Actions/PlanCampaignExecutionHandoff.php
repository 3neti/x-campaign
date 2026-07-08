<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionHandoffs;
use LBHurtado\XCampaign\Data\CampaignBatchData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffData;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffResultData;

class PlanCampaignExecutionHandoff implements PlansCampaignExecutionHandoffs
{
    public function plan(CampaignExecutionHandoffData $handoff): CampaignExecutionHandoffResultData
    {
        $planningKey = trim($handoff->planningKey);

        if ($planningKey === '') {
            throw new InvalidArgumentException('Campaign execution handoff planning key is required.');
        }

        $blockers = $this->blockers($handoff);
        $execution = $handoff->executionPlan->execution;

        return new CampaignExecutionHandoffResultData(
            status: $blockers === [] ? 'ready' : 'blocked',
            handoffId: $this->handoffId($handoff),
            handoff: $handoff,
            blockers: $blockers,
            metadata: [
                'planning_key' => $planningKey,
                'campaign_id' => $execution->campaignId,
                'audience_id' => $execution->audienceId,
                'execution_id' => $execution->id,
                'batch_count' => count($handoff->executionPlan->batches),
                'recipient_count' => $this->recipientCount($handoff),
                'requested_by' => $handoff->requestedBy,
                'correlation_id' => $handoff->correlationId,
                'handoff_only' => true,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignExecutionHandoffData $handoff): array
    {
        $blockers = [];

        if ($handoff->executionPlan->execution->status !== 'planned') {
            $blockers[] = 'Execution handoff requires a planned execution.';
        }

        if ($handoff->executionPlan->batches === []) {
            $blockers[] = 'Execution handoff requires at least one planned batch.';
        }

        return $blockers;
    }

    private function handoffId(CampaignExecutionHandoffData $handoff): string
    {
        $execution = $handoff->executionPlan->execution;

        return 'handoff-'.substr(hash('sha256', implode('|', [
            trim($handoff->planningKey),
            $execution->id,
            (string) count($handoff->executionPlan->batches),
            (string) $this->recipientCount($handoff),
        ])), 0, 16);
    }

    private function recipientCount(CampaignExecutionHandoffData $handoff): int
    {
        return array_reduce(
            $handoff->executionPlan->batches,
            fn (int $total, mixed $batch): int => $total + ($batch instanceof CampaignBatchData ? $batch->recipientCount : 0),
            0,
        );
    }
}
