<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel;

it('binds attachment operator workspace collection operator summary contracts to the read-only baseline', function () {
    expect(app(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries::class))->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel::class);
});
