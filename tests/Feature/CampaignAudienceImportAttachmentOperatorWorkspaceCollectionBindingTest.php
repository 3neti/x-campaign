<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel;

it('binds audience import attachment operator workspace collections to side effect free read models', function () {
    expect(app(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections::class))->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel::class);
});
