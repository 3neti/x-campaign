<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperatorReports;
use LBHurtado\XCampaign\ReadModels\CampaignOperatorReportBuilder;

it('binds operator reports to the read-only report builder', function () {
    expect(app(BuildsCampaignOperatorReports::class))->toBeInstanceOf(CampaignOperatorReportBuilder::class);
});
