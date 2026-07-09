<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessChecklistData;

interface BuildsCampaignProductionReadinessAssessments
{
    public function build(CampaignProductionReadinessChecklistData $checklist): CampaignProductionReadinessAssessmentData;
}
