<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Contracts\CampaignRecipientImportRowWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

function campaignRecipientImportRowWorkspace(): RepositoryBackedCampaignRecipientImportRowWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Recipient Import Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-recipients', name: 'Recipient Import Audience'),
    );

    $repository->put('planning-recipient-imports', $plan);

    return new RepositoryBackedCampaignRecipientImportRowWorkspace(
        repository: $repository,
        planner: new PlanCampaignRecipientImportRow,
    );
}

it('plans a recipient import row against a stored campaign audience without mutating recipients', function () {
    $workspace = campaignRecipientImportRowWorkspace();

    $planned = $workspace->plan('planning-recipient-imports', new CampaignRecipientImportRowPlanningInputData(
        importId: 'import-row-workspace-001',
        audienceId: 'audience-recipients',
        rowNumber: 12,
        row: [
            'full_name' => 'Ana Reyes',
            'phone' => '0917 000 0012',
            'email' => ' ANA@EXAMPLE.TEST ',
            'external_id' => 'EXT-012',
        ],
    ));

    expect($workspace)->toBeInstanceOf(CampaignRecipientImportRowWorkspace::class)
        ->and($planned->status)->toBe('valid')
        ->and($planned->recipient->name)->toBe('Ana Reyes')
        ->and($planned->recipient->mobile)->toBe('09170000012')
        ->and($planned->recipient->email)->toBe('ana@example.test')
        ->and($planned->recipient->externalReference)->toBe('EXT-012')
        ->and($planned->metadata['planning_key'])->toBe('planning-recipient-imports')
        ->and($planned->metadata['audience_name'])->toBe('Recipient Import Audience')
        ->and($planned->metadata['existing_recipient_count'])->toBe(0)
        ->and($planned->effects)->toMatchArray([
            'integrates_repository' => true,
            'parses_files' => false,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('returns row diagnostics while still validating campaign and audience context', function () {
    $workspace = campaignRecipientImportRowWorkspace();

    $planned = $workspace->plan('planning-recipient-imports', new CampaignRecipientImportRowPlanningInputData(
        importId: 'import-row-workspace-002',
        audienceId: 'audience-recipients',
        rowNumber: 13,
        row: [
            'external_reference' => 'EXT-013',
        ],
    ));

    expect($planned->status)->toBe('invalid')
        ->and($planned->errors)->toBe([
            'name' => 'Recipient name is required.',
            'contact' => 'At least one recipient contact field is required.',
        ])
        ->and($planned->metadata['planning_key'])->toBe('planning-recipient-imports')
        ->and($planned->metadata['audience_name'])->toBe('Recipient Import Audience');
});

it('fails closed when planning a recipient row for an unknown planning key', function () {
    $workspace = campaignRecipientImportRowWorkspace();

    expect(fn () => $workspace->plan('missing-planning', new CampaignRecipientImportRowPlanningInputData(
        audienceId: 'audience-recipients',
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed when planning a recipient row for an audience outside the stored plan', function () {
    $workspace = campaignRecipientImportRowWorkspace();

    expect(fn () => $workspace->plan('planning-recipient-imports', new CampaignRecipientImportRowPlanningInputData(
        audienceId: 'missing-audience',
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});
