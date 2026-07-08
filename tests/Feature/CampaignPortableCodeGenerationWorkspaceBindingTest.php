<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPortableCodeGenerationWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPortableCodeGenerationWorkspace;

it('binds portable code generation workspace contracts to the repository-backed workspace', function () {
    $workspace = app(CampaignPortableCodeGenerationWorkspace::class);

    expect($workspace)
        ->toBeInstanceOf(CampaignPortableCodeGenerationWorkspace::class)
        ->toBeInstanceOf(RepositoryBackedCampaignPortableCodeGenerationWorkspace::class)
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

