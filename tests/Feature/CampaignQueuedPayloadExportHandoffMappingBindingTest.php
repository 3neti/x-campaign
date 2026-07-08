<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExportHandoffs;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadExportHandoffMapper;

it('binds queued export handoff payload mapping to the export handoff mapper', function () {
    expect(app(MapsCampaignQueuedPayloadsToExportHandoffs::class))
        ->toBeInstanceOf(CampaignQueuedPayloadExportHandoffMapper::class);
});
