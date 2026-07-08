<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignPortableCodeGeneration;
use LBHurtado\XCampaign\Contracts\PlansCampaignPortableCodeGenerations;

it('binds portable code generation planning to the in-memory planner', function () {
    expect(app(PlansCampaignPortableCodeGenerations::class))->toBeInstanceOf(PlanCampaignPortableCodeGeneration::class);
});
