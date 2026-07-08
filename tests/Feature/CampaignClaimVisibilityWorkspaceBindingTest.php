<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignClaimStatusProvider;
use LBHurtado\XCampaign\Contracts\CampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Gateways\NullCampaignClaimStatusProvider;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignClaimVisibilityWorkspace;

it('binds claim visibility workspace contracts to the repository-backed workspace', function () {
    $workspace = app(CampaignClaimVisibilityWorkspace::class);

    expect($workspace)
        ->toBeInstanceOf(CampaignClaimVisibilityWorkspace::class)
        ->toBeInstanceOf(RepositoryBackedCampaignClaimVisibilityWorkspace::class)
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

it('binds claim status provider to a safe null provider', function () {
    expect(app(CampaignClaimStatusProvider::class))->toBeInstanceOf(NullCampaignClaimStatusProvider::class);
});

