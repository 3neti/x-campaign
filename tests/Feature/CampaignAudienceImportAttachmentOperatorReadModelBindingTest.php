<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorReadModels;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorReadModel;

it('binds audience import attachment operator read models to side effect free implementations', function () {
    expect(app(BuildsCampaignAudienceImportAttachmentOperatorReadModels::class))->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorReadModel::class);
});
