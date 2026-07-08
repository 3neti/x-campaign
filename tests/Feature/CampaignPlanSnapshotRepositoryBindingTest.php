<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Repositories\EloquentCampaignPlanSnapshotRepository;

it('binds campaign plan snapshot repository to the eloquent durable baseline', function () {
    expect(app(CampaignPlanSnapshotRepository::class))->toBeInstanceOf(EloquentCampaignPlanSnapshotRepository::class);
});
