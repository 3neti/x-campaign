<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperationalHealthSnapshots;
use LBHurtado\XCampaign\ReadModels\CampaignOperationalHealthSnapshotBuilder;

it('binds operational health snapshot building to the read-only builder', function () {
    expect(app(BuildsCampaignOperationalHealthSnapshots::class))
        ->toBeInstanceOf(CampaignOperationalHealthSnapshotBuilder::class);
});
