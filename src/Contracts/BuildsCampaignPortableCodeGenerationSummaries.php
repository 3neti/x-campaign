<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationSummaryData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationWorkspaceResultData;

interface BuildsCampaignPortableCodeGenerationSummaries
{
    public function fromWorkspaceResult(CampaignPortableCodeGenerationWorkspaceResultData $result): CampaignPortableCodeGenerationSummaryData;
}
