<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToClaimVisibilities;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadClaimVisibilityMapper;

it('binds queued payload claim visibility mapping to the baseline mapper', function () {
    expect(app(MapsCampaignQueuedPayloadsToClaimVisibilities::class))->toBeInstanceOf(CampaignQueuedPayloadClaimVisibilityMapper::class);
});
