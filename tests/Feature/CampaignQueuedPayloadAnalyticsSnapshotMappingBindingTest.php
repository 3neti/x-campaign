<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToAnalyticsSnapshots;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadAnalyticsSnapshotMapper;

it('binds queued analytics payload mapping to the analytics mapper', function () {
    expect(app(MapsCampaignQueuedPayloadsToAnalyticsSnapshots::class))
        ->toBeInstanceOf(CampaignQueuedPayloadAnalyticsSnapshotMapper::class);
});
