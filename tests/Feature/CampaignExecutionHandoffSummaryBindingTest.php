<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignExecutionHandoffSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignExecutionHandoffSummaryReadModel;

it('binds execution handoff summaries to the read-only summary read model', function () {
    expect(app(BuildsCampaignExecutionHandoffSummaries::class))->toBeInstanceOf(CampaignExecutionHandoffSummaryReadModel::class);
});
