<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionBatches;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutions;

it('binds campaign execution planning contracts to in-memory implementations', function () {
    expect(app(PlansCampaignExecutions::class))->toBeInstanceOf(PlansCampaignExecutions::class)
        ->and(app(PlansCampaignExecutionBatches::class))->toBeInstanceOf(PlansCampaignExecutionBatches::class);
});

