<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignDeliveryHandoffWorkspace;

it('binds delivery handoff workspace contracts to the repository-backed workspace', function () {
    $workspace = app(CampaignDeliveryHandoffWorkspace::class);

    expect($workspace)
        ->toBeInstanceOf(CampaignDeliveryHandoffWorkspace::class)
        ->toBeInstanceOf(RepositoryBackedCampaignDeliveryHandoffWorkspace::class)
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

