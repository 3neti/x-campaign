<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiEndpointRecommendationMatrices;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiEndpointRecommendationMatrixBuilder;

it('binds the public api endpoint recommendation matrix builder contract', function () {
    expect(app(BuildsCampaignPublicApiEndpointRecommendationMatrices::class))
        ->toBeInstanceOf(CampaignPublicApiEndpointRecommendationMatrixBuilder::class);
});
