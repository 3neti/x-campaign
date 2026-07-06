<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignRecipientImportRowWorkspace;

it('binds the recipient import row workspace to the repository-backed non-importing baseline', function () {
    expect(app(CampaignRecipientImportRowWorkspace::class))->toBeInstanceOf(CampaignRecipientImportRowWorkspace::class);
});
