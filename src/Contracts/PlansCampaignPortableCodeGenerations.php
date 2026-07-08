<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;

interface PlansCampaignPortableCodeGenerations
{
    public function plan(CampaignPortableCodeGenerationRequestData $request): CampaignPortableCodeGenerationResultData;
}
