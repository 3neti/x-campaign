<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImport;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportWorkspace;

function campaignAudienceImportWorkspace(): RepositoryBackedCampaignAudienceImportWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Import Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-imports', name: 'Import Audience'),
    );

    $repository->put('planning-imports', $plan);

    return new RepositoryBackedCampaignAudienceImportWorkspace(
        repository: $repository,
        planner: new PlanCampaignAudienceImport,
    );
}

it('plans an audience import against a stored campaign audience without parsing or persistence', function () {
    $workspace = campaignAudienceImportWorkspace();

    $planned = $workspace->plan('planning-imports', new CampaignAudienceImportPlanningInputData(
        id: 'import-workspace-001',
        audienceId: 'audience-imports',
        source: 'spreadsheet',
        sourceReference: 'batch.xlsx',
        expectedRecipientCount: 25,
        columns: ['name', 'mobile'],
    ));

    expect($workspace)->toBeInstanceOf(CampaignAudienceImportWorkspace::class)
        ->and($planned->import->id)->toBe('import-workspace-001')
        ->and($planned->import->audienceId)->toBe('audience-imports')
        ->and($planned->import->status)->toBe('planned')
        ->and($planned->import->recipientCount)->toBe(25)
        ->and($planned->metadata['planning_key'])->toBe('planning-imports')
        ->and($planned->metadata['audience_name'])->toBe('Import Audience')
        ->and($planned->effects)->toMatchArray([
            'parses_files' => false,
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('fails closed when planning an import for an unknown planning key', function () {
    $workspace = campaignAudienceImportWorkspace();

    expect(fn () => $workspace->plan('missing-planning', new CampaignAudienceImportPlanningInputData(
        audienceId: 'audience-imports',
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed when planning an import for an audience outside the stored plan', function () {
    $workspace = campaignAudienceImportWorkspace();

    expect(fn () => $workspace->plan('planning-imports', new CampaignAudienceImportPlanningInputData(
        audienceId: 'missing-audience',
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});

