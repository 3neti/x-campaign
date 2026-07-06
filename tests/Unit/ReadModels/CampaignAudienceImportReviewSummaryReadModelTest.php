<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRowCollection;
use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportReviewSummaries;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewRowIssueData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportReviewSummaryReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

function campaignAudienceImportReviewCollection(): LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Review Summary Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-review', name: 'Review Audience'),
    );

    $repository->put('planning-review-summary', $plan);

    return (new PlanCampaignAudienceImportRowCollection(
        rowWorkspace: new RepositoryBackedCampaignRecipientImportRowWorkspace(
            repository: $repository,
            planner: new PlanCampaignRecipientImportRow,
        ),
    ))->handle('planning-review-summary', new CampaignAudienceImportRowCollectionPlanningInputData(
        importId: 'import-review-001',
        audienceId: 'audience-review',
        startingRowNumber: 5,
        rows: [
            [
                'full_name' => 'Ana Reyes',
                'phone' => '0917 000 0001',
                'external_id' => 'EXT-001',
            ],
            [
                'external_reference' => 'EXT-002',
            ],
        ],
    ));
}

it('builds a read-only audience import review summary from a row collection plan', function () {
    $summary = (new CampaignAudienceImportReviewSummaryReadModel)->fromCollectionPlan(
        campaignAudienceImportReviewCollection(),
    );

    expect(new CampaignAudienceImportReviewSummaryReadModel)
        ->toBeInstanceOf(BuildsCampaignAudienceImportReviewSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignAudienceImportReviewSummaryData::class)
        ->and($summary->importId)->toBe('import-review-001')
        ->and($summary->audienceId)->toBe('audience-review')
        ->and($summary->status)->toBe('review_required')
        ->and($summary->totalRows)->toBe(2)
        ->and($summary->validRows)->toBe(1)
        ->and($summary->invalidRows)->toBe(1)
        ->and($summary->readyForApproval)->toBeFalse()
        ->and($summary->validRowNumbers)->toBe([5])
        ->and($summary->invalidRowNumbers)->toBe([6])
        ->and($summary->issues)->toHaveCount(1)
        ->and($summary->issues[0])->toBeInstanceOf(CampaignAudienceImportReviewRowIssueData::class)
        ->and($summary->issues[0]->rowNumber)->toBe(6)
        ->and($summary->issues[0]->errors)->toBe([
            'name' => 'Recipient name is required.',
            'contact' => 'At least one recipient contact field is required.',
        ])
        ->and($summary->effects)->toMatchArray([
            'parses_files' => false,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($summary->metadata['source'])->toBe('campaign-audience-import-review-summary-read-model');
});

it('marks all-valid row collections as ready for approval without mutating the audience', function () {
    $collection = campaignAudienceImportReviewCollection();
    $validOnly = new LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData(
        importId: $collection->importId,
        audienceId: $collection->audienceId,
        status: 'valid',
        totalRows: 1,
        validRows: 1,
        invalidRows: 0,
        rows: [$collection->rows[0]],
        effects: $collection->effects,
        metadata: [
            ...$collection->metadata,
            'valid_row_numbers' => [5],
            'invalid_row_numbers' => [],
        ],
    );

    $summary = (new CampaignAudienceImportReviewSummaryReadModel)->fromCollectionPlan($validOnly);

    expect($summary->status)->toBe('ready')
        ->and($summary->readyForApproval)->toBeTrue()
        ->and($summary->issues)->toBe([])
        ->and($summary->validRowNumbers)->toBe([5])
        ->and($summary->invalidRowNumbers)->toBe([]);
});

it('marks empty row collections as empty and not ready for approval', function () {
    $summary = (new CampaignAudienceImportReviewSummaryReadModel)->fromCollectionPlan(
        new LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData(
            importId: 'import-empty-review',
            audienceId: 'audience-review',
        ),
    );

    expect($summary->status)->toBe('empty')
        ->and($summary->readyForApproval)->toBeFalse()
        ->and($summary->totalRows)->toBe(0)
        ->and($summary->validRows)->toBe(0)
        ->and($summary->invalidRows)->toBe(0)
        ->and($summary->issues)->toBe([]);
});
