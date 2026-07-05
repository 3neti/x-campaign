<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\AddsAudiencesToCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignAudienceData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class AddAudienceToCampaignPlan implements AddsAudiencesToCampaignPlans
{
    public function handle(CampaignPlanData $plan, CampaignAudiencePlanningInputData $input): CampaignPlanData
    {
        $audience = new CampaignAudiencePlanData(
            audience: new CampaignAudienceData(
                id: $input->id ?: $this->identifier($input->name),
                name: $input->name,
                status: $input->status,
                metadata: $input->metadata,
            ),
            effects: $this->effects(),
            metadata: [
                'source' => 'in-memory-audience-planning',
            ],
        );

        return new CampaignPlanData(
            campaign: $plan->campaign,
            audiences: [
                ...$plan->audiences,
                $audience,
            ],
            executions: $plan->executions,
            effects: [
                ...$plan->effects,
                ...$this->effects(),
            ],
            metadata: [
                ...$plan->metadata,
                'audience_planning' => 'in-memory',
            ],
        );
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'persists' => false,
            'imports_files' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }

    private function identifier(string $name): string
    {
        $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name) ?: 'audience'));

        return trim($id, '-') ?: 'audience';
    }
}
