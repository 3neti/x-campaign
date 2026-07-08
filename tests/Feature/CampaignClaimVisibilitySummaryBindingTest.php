<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignClaimVisibilitySummaries;
use LBHurtado\XCampaign\ReadModels\CampaignClaimVisibilitySummaryReadModel;

it('binds claim visibility summaries to the read-only read model', function () {
    expect(app(BuildsCampaignClaimVisibilitySummaries::class))->toBeInstanceOf(CampaignClaimVisibilitySummaryReadModel::class);
});

