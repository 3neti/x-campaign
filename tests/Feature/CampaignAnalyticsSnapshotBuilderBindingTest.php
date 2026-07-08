<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsSnapshots;
use LBHurtado\XCampaign\ReadModels\CampaignAnalyticsSnapshotBuilder;

it('binds analytics snapshots to the read-only snapshot builder', function () {
    expect(app(BuildsCampaignAnalyticsSnapshots::class))->toBeInstanceOf(CampaignAnalyticsSnapshotBuilder::class);
});
