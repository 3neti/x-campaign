<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel;

it('binds recipient attachment mutation summary read models to side effect free implementations', function () {
    expect(app(BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries::class))->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel::class);
});
