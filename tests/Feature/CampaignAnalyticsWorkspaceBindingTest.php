<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignAnalyticsWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAnalyticsWorkspace;

it('binds analytics workspace contracts to the repository-backed workspace', function () {
    $workspace = app(CampaignAnalyticsWorkspace::class);

    expect($workspace)
        ->toBeInstanceOf(CampaignAnalyticsWorkspace::class)
        ->toBeInstanceOf(RepositoryBackedCampaignAnalyticsWorkspace::class)
        ->and($workspace->effects())->toMatchArray([
            'integrates_repository' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});
