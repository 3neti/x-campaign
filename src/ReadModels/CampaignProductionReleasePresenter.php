<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\PresentsCampaignProductionReleases;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReleaseData;

class CampaignProductionReleasePresenter implements PresentsCampaignProductionReleases
{
    public function present(CampaignProductionReadinessAssessmentData $assessment): CampaignProductionReleaseData
    {
        return new CampaignProductionReleaseData(
            status: $assessment->status === 'ready' ? 'releasable' : $assessment->status,
            summary: [
                'planning_key' => $assessment->planningKey,
                'execution_id' => $assessment->executionId,
                'operator_id' => $assessment->operatorId,
                'readiness_status' => $assessment->status,
                'checks' => $assessment->checks,
                'blockers' => $assessment->blockers,
            ],
            meta: [
                ...$assessment->metadata,
                'source' => 'campaign-production-release-presenter',
                'read_only' => true,
                'deploys' => false,
                'writes_environment' => false,
                'starts_workers' => false,
            ],
        );
    }
}
