<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignSummaryReadModel;

it('binds campaign summary read model contracts to side effect free implementations', function () {
    expect(app(BuildsCampaignSummaries::class))->toBeInstanceOf(CampaignSummaryReadModel::class);
});
