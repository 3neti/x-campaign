<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToDeliveryHandoffs;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadDeliveryHandoffMapper;

it('binds queued payload delivery handoff mapping to the baseline mapper', function () {
    expect(app(MapsCampaignQueuedPayloadsToDeliveryHandoffs::class))->toBeInstanceOf(CampaignQueuedPayloadDeliveryHandoffMapper::class);
});
