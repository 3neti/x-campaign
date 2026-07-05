<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\ArchivesCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class ArchiveCampaignPlan implements ArchivesCampaignPlans
{
    public function handle(CampaignPlanData $plan, ?string $reason = null): CampaignPlanData
    {
        return new CampaignPlanData(
            campaign: new CampaignData(
                id: $plan->campaign->id,
                name: $plan->campaign->name,
                description: $plan->campaign->description,
                featureProfile: $plan->campaign->featureProfile,
                owner: $plan->campaign->owner,
                issuer: $plan->campaign->issuer,
                status: 'archived',
                scheduledAt: $plan->campaign->scheduledAt,
                metadata: $plan->campaign->metadata,
            ),
            audiences: $plan->audiences,
            executions: $plan->executions,
            effects: [
                ...$plan->effects,
                'deletes_records' => false,
            ],
            metadata: [
                ...$plan->metadata,
                'archive_reason' => $reason,
            ],
        );
    }
}
