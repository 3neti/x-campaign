<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsOperatorSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignAnalyticsOperatorSummaryReadModel;

it('binds analytics operator summaries to the read-only read model', function () {
    expect(app(BuildsCampaignAnalyticsOperatorSummaries::class))
        ->toBeInstanceOf(CampaignAnalyticsOperatorSummaryReadModel::class);
});
