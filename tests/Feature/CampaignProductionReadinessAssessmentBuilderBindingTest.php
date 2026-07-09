<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignProductionReadinessAssessments;
use LBHurtado\XCampaign\ReadModels\CampaignProductionReadinessAssessmentBuilder;

it('binds the production readiness assessment builder contract', function () {
    expect(app(BuildsCampaignProductionReadinessAssessments::class))
        ->toBeInstanceOf(CampaignProductionReadinessAssessmentBuilder::class);
});
