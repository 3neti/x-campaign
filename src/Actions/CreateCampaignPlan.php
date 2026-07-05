<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

class CreateCampaignPlan implements CreatesCampaignPlans
{
    public function handle(CampaignPlanningInputData $input): CampaignPlanData
    {
        return new CampaignPlanData(
            campaign: new CampaignData(
                name: $input->name,
                description: $input->description,
                featureProfile: $input->featureProfile ?: 'baseline',
                owner: $input->owner,
                issuer: $input->issuer,
                status: 'draft',
                scheduledAt: $input->scheduledAt,
                metadata: $input->metadata,
            ),
            effects: $this->planningEffects(),
            metadata: [
                'source' => 'in-memory-planning',
                'persists_plan' => false,
                'executes_distribution' => false,
            ],
        );
    }

    /**
     * @return array<string, bool>
     */
    private function planningEffects(): array
    {
        return [
            'persists' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }
}
