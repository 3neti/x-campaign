<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignRecipientImportRows;

it('binds recipient import row planning contracts to the non-importing baseline', function () {
    expect(app(PlansCampaignRecipientImportRows::class))->toBeInstanceOf(PlansCampaignRecipientImportRows::class);
});
