<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPortableCodeGenerationSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignPortableCodeGenerationSummaryReadModel;

it('binds portable code generation summaries to the read-only summary read model', function () {
    expect(app(BuildsCampaignPortableCodeGenerationSummaries::class))
        ->toBeInstanceOf(CampaignPortableCodeGenerationSummaryReadModel::class);
});

