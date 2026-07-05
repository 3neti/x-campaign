<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceSummaryData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignExecutionSummaryData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;

class CampaignSummaryReadModel implements BuildsCampaignSummaries
{
    public function fromPlan(CampaignPlanData $plan): CampaignSummaryData
    {
        $audiences = $this->audiences($plan);
        $executions = $this->executions($plan);

        return new CampaignSummaryData(
            campaignId: $plan->campaign->id,
            name: $plan->campaign->name,
            status: $plan->campaign->status,
            featureProfile: $plan->campaign->featureProfile,
            owner: $plan->campaign->owner,
            issuer: $plan->campaign->issuer,
            scheduledAt: $plan->campaign->scheduledAt,
            audienceCount: count($audiences),
            recipientCount: array_sum(array_map(
                fn (CampaignAudienceSummaryData $audience): int => $audience->recipientCount,
                $audiences,
            )),
            executionCount: count($executions),
            batchCount: array_sum(array_map(
                fn (CampaignExecutionSummaryData $execution): int => $execution->batchCount,
                $executions,
            )),
            plannedRecipientCount: array_sum(array_map(
                fn (CampaignExecutionSummaryData $execution): int => $execution->plannedRecipientCount,
                $executions,
            )),
            audiences: $audiences,
            executions: $executions,
            effects: [
                ...$plan->effects,
                ...$this->effects(),
            ],
            metadata: [
                'source' => 'campaign-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    /**
     * @return array<int, CampaignAudienceSummaryData>
     */
    private function audiences(CampaignPlanData $plan): array
    {
        return array_values(array_map(
            fn (CampaignAudiencePlanData $audience): CampaignAudienceSummaryData => new CampaignAudienceSummaryData(
                audienceId: $audience->audience->id,
                name: $audience->audience->name,
                status: $audience->audience->status,
                recipientCount: count($audience->recipients),
                metadata: [
                    'source' => 'campaign-audience-summary-read-model',
                ],
            ),
            $plan->audiences,
        ));
    }

    /**
     * @return array<int, CampaignExecutionSummaryData>
     */
    private function executions(CampaignPlanData $plan): array
    {
        return array_values(array_map(
            fn (CampaignExecutionPlanData $execution): CampaignExecutionSummaryData => new CampaignExecutionSummaryData(
                executionId: $execution->execution->id,
                audienceId: $execution->execution->audienceId,
                status: $execution->execution->status,
                scheduledAt: $execution->execution->scheduledAt,
                batchCount: count($execution->batches),
                plannedRecipientCount: (int) ($execution->metadata['recipient_count'] ?? 0),
                metadata: [
                    'source' => 'campaign-execution-summary-read-model',
                ],
            ),
            $plan->executions,
        ));
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

