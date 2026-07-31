<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\AddsAudiencesToCampaignPlans;
use LBHurtado\XCampaign\Contracts\AddsRecipientsToCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\RemovesRecipientsFromCampaignAudiencePlans;

it('binds audience and recipient planning contracts to in-memory implementations', function () {
    expect(app(AddsAudiencesToCampaignPlans::class))->toBeInstanceOf(AddsAudiencesToCampaignPlans::class)
        ->and(app(AddsRecipientsToCampaignAudiencePlans::class))->toBeInstanceOf(AddsRecipientsToCampaignAudiencePlans::class)
        ->and(app(RemovesRecipientsFromCampaignAudiencePlans::class))->toBeInstanceOf(RemovesRecipientsFromCampaignAudiencePlans::class);
});
