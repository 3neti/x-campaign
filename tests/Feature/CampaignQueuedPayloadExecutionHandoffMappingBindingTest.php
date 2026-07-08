<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExecutionHandoffs;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadExecutionHandoffMapper;

it('binds queued payload execution handoff mapping to the baseline mapper', function () {
    expect(app(MapsCampaignQueuedPayloadsToExecutionHandoffs::class))->toBeInstanceOf(CampaignQueuedPayloadExecutionHandoffMapper::class);
});
