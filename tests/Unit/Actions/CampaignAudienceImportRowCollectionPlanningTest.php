<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRowCollection;
use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRowCollections;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

function campaignAudienceImportRowCollectionPlanner(): PlanCampaignAudienceImportRowCollection
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Audience Row Collection Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-row-collection', name: 'Row Collection Audience'),
    );

    $repository->put('planning-row-collection', $plan);

    return new PlanCampaignAudienceImportRowCollection(
        rowWorkspace: new RepositoryBackedCampaignRecipientImportRowWorkspace(
            repository: $repository,
            planner: new PlanCampaignRecipientImportRow,
        ),
    );
}

it('plans a collection of recipient import rows without parsing files or mutating audiences', function () {
    $planned = campaignAudienceImportRowCollectionPlanner()->handle(
        'planning-row-collection',
        new CampaignAudienceImportRowCollectionPlanningInputData(
            importId: 'import-collection-001',
            audienceId: 'audience-row-collection',
            startingRowNumber: 2,
            rows: [
                [
                    'full_name' => 'Ana Reyes',
                    'phone' => '0917 000 0001',
                    'external_id' => 'EXT-001',
                ],
                [
                    'name' => 'Ben Cruz',
                    'email' => ' BEN@EXAMPLE.TEST ',
                    'external_reference' => 'EXT-002',
                ],
                [
                    'external_reference' => 'EXT-003',
                ],
            ],
        ),
    );

    expect(new PlanCampaignAudienceImportRowCollection(
        rowWorkspace: new RepositoryBackedCampaignRecipientImportRowWorkspace(
            repository: new InMemoryCampaignPlanRepository,
            planner: new PlanCampaignRecipientImportRow,
        ),
    ))->toBeInstanceOf(PlansCampaignAudienceImportRowCollections::class)
        ->and($planned)->toBeInstanceOf(CampaignAudienceImportRowCollectionPlanData::class)
        ->and($planned->importId)->toBe('import-collection-001')
        ->and($planned->audienceId)->toBe('audience-row-collection')
        ->and($planned->status)->toBe('invalid')
        ->and($planned->totalRows)->toBe(3)
        ->and($planned->validRows)->toBe(2)
        ->and($planned->invalidRows)->toBe(1)
        ->and($planned->rows)->toHaveCount(3)
        ->and($planned->rows[0]->rowNumber)->toBe(2)
        ->and($planned->rows[0]->recipient->name)->toBe('Ana Reyes')
        ->and($planned->rows[1]->rowNumber)->toBe(3)
        ->and($planned->rows[1]->recipient->email)->toBe('ben@example.test')
        ->and($planned->rows[2]->rowNumber)->toBe(4)
        ->and($planned->rows[2]->status)->toBe('invalid')
        ->and($planned->metadata['planning_key'])->toBe('planning-row-collection')
        ->and($planned->metadata['valid_row_numbers'])->toBe([2, 3])
        ->and($planned->metadata['invalid_row_numbers'])->toBe([4])
        ->and($planned->effects)->toMatchArray([
            'uses_row_workspace' => true,
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

it('plans an empty row collection as an empty reviewable collection', function () {
    $planned = campaignAudienceImportRowCollectionPlanner()->handle(
        'planning-row-collection',
        new CampaignAudienceImportRowCollectionPlanningInputData(
            importId: 'import-collection-empty',
            audienceId: 'audience-row-collection',
            rows: [],
        ),
    );

    expect($planned->status)->toBe('empty')
        ->and($planned->totalRows)->toBe(0)
        ->and($planned->validRows)->toBe(0)
        ->and($planned->invalidRows)->toBe(0)
        ->and($planned->rows)->toBe([])
        ->and($planned->metadata['valid_row_numbers'])->toBe([])
        ->and($planned->metadata['invalid_row_numbers'])->toBe([]);
});

it('fails closed through row workspace context validation before planning collection rows', function () {
    expect(fn () => campaignAudienceImportRowCollectionPlanner()->handle(
        'planning-row-collection',
        new CampaignAudienceImportRowCollectionPlanningInputData(
            importId: 'import-collection-missing-audience',
            audienceId: 'missing-audience',
            rows: [
                ['name' => 'Ana Reyes', 'mobile' => '09170000001'],
            ],
        ),
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});
