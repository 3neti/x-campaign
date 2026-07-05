<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutions;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanData;
use LBHurtado\XCampaign\Data\CampaignExecutionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class PlanCampaignExecution implements PlansCampaignExecutions
{
    public function handle(CampaignPlanData $plan, CampaignExecutionPlanningInputData $input): CampaignPlanData
    {
        $audience = $this->audience($plan, $input->audienceId);
        $executionId = $input->id ?: $this->identifier($plan, $audience);

        $execution = new CampaignExecutionPlanData(
            execution: new CampaignExecutionData(
                id: $executionId,
                campaignId: $plan->campaign->id,
                audienceId: $audience->audience->id,
                status: 'planned',
                scheduledAt: $input->scheduledAt,
                correlationId: $input->correlationId,
                metadata: $input->metadata,
            ),
            effects: $this->effects(),
            metadata: [
                'source' => 'in-memory-execution-planning',
                'recipient_count' => count($audience->recipients),
            ],
        );

        return new CampaignPlanData(
            campaign: $plan->campaign,
            audiences: $plan->audiences,
            executions: [
                ...$plan->executions,
                $execution,
            ],
            effects: [
                ...$plan->effects,
                ...$this->effects(),
            ],
            metadata: [
                ...$plan->metadata,
                'execution_planning' => 'in-memory',
            ],
        );
    }

    private function audience(CampaignPlanData $plan, ?string $audienceId): CampaignAudiencePlanData
    {
        foreach ($plan->audiences as $audience) {
            if ($audience instanceof CampaignAudiencePlanData && $audience->audience->id === $audienceId) {
                return $audience;
            }
        }

        throw new InvalidArgumentException("Unknown campaign audience plan [{$audienceId}].");
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

    private function identifier(CampaignPlanData $plan, CampaignAudiencePlanData $audience): string
    {
        $campaign = $plan->campaign->id ?: strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $plan->campaign->name) ?: 'campaign'));
        $audienceId = $audience->audience->id ?: 'audience';

        return trim($campaign, '-').'-'.$audienceId.'-execution';
    }
}
