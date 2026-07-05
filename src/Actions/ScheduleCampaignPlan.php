<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class ScheduleCampaignPlan implements SchedulesCampaignPlans
{
    public function handle(CampaignPlanData $plan, string $scheduledAt): CampaignPlanData
    {
        return new CampaignPlanData(
            campaign: new CampaignData(
                id: $plan->campaign->id,
                name: $plan->campaign->name,
                description: $plan->campaign->description,
                featureProfile: $plan->campaign->featureProfile,
                owner: $plan->campaign->owner,
                issuer: $plan->campaign->issuer,
                status: 'scheduled',
                scheduledAt: $scheduledAt,
                metadata: $plan->campaign->metadata,
            ),
            audiences: $plan->audiences,
            executions: $plan->executions,
            effects: $plan->effects,
            metadata: [
                ...$plan->metadata,
                'scheduled_without_dispatch' => true,
            ],
        );
    }
}
