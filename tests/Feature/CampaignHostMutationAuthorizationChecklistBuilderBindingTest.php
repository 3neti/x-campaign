<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostMutationAuthorizationChecklists;
use LBHurtado\XCampaign\ReadModels\CampaignHostMutationAuthorizationChecklistBuilder;

it('binds the host mutation authorization checklist builder contract', function () {
    expect(app(BuildsCampaignHostMutationAuthorizationChecklists::class))
        ->toBeInstanceOf(CampaignHostMutationAuthorizationChecklistBuilder::class);
});
