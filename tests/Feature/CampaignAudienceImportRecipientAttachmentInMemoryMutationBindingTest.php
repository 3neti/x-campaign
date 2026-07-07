<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\AttachesCampaignAudienceImportRecipientsInMemory;

it('binds in-memory recipient attachment mutation contracts to the in-memory baseline', function () {
    expect(app(AttachesCampaignAudienceImportRecipientsInMemory::class))->toBeInstanceOf(AttachesCampaignAudienceImportRecipientsInMemory::class);
});

