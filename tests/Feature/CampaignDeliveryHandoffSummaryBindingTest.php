<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignDeliveryHandoffSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignDeliveryHandoffSummaryReadModel;

it('binds delivery handoff summaries to the read-only read model', function () {
    expect(app(BuildsCampaignDeliveryHandoffSummaries::class))->toBeInstanceOf(CampaignDeliveryHandoffSummaryReadModel::class);
});
