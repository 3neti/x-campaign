<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReleaseData;

interface PresentsCampaignProductionReleases
{
    public function present(CampaignProductionReadinessAssessmentData $assessment): CampaignProductionReleaseData;
}
