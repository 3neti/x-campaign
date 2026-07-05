<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;

class UpdateCampaignPlan implements UpdatesCampaignPlans
{
    public function handle(CampaignPlanData $plan, CampaignPlanningInputData $input): CampaignPlanData
    {
        return new CampaignPlanData(
            campaign: new CampaignData(
                id: $plan->campaign->id,
                name: $input->name !== '' ? $input->name : $plan->campaign->name,
                description: $input->description ?? $plan->campaign->description,
                featureProfile: $input->featureProfile ?? $plan->campaign->featureProfile,
                owner: $input->owner ?? $plan->campaign->owner,
                issuer: $input->issuer ?? $plan->campaign->issuer,
                status: $plan->campaign->status,
                scheduledAt: $input->scheduledAt ?? $plan->campaign->scheduledAt,
                metadata: $input->metadata ?: $plan->campaign->metadata,
            ),
            audiences: $plan->audiences,
            executions: $plan->executions,
            effects: $plan->effects,
            metadata: [
                ...$plan->metadata,
                'updated_in_memory' => true,
            ],
        );
    }
}
