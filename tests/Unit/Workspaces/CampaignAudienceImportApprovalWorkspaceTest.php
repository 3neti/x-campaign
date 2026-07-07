<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportApproval;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRowCollection;
use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportReviewSummaryReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

function campaignAudienceImportApprovalWorkspace(): RepositoryBackedCampaignAudienceImportApprovalWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Approval Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-approval-workspace', name: 'Approval Workspace Audience'),
    );

    $repository->put('planning-approval-workspace', $plan);

    return new RepositoryBackedCampaignAudienceImportApprovalWorkspace(
        repository: $repository,
        rowCollectionPlanner: new PlanCampaignAudienceImportRowCollection(
            rowWorkspace: new RepositoryBackedCampaignRecipientImportRowWorkspace(
                repository: $repository,
                planner: new PlanCampaignRecipientImportRow,
            ),
        ),
        reviewSummary: new CampaignAudienceImportReviewSummaryReadModel,
        approvalDecider: new DecideCampaignAudienceImportApproval,
    );
}

it('approves an audience import through the workspace without mutating recipients or dispatching work', function () {
    $workspace = campaignAudienceImportApprovalWorkspace();

    $result = $workspace->decide('planning-approval-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-workspace-approval-001',
        audienceId: 'audience-approval-workspace',
        decision: 'approve',
        decidedBy: 'operator-001',
        reason: 'Reviewed valid rows.',
        rows: [
            [
                'full_name' => 'Ana Reyes',
                'phone' => '0917 000 0001',
            ],
            [
                'name' => 'Ben Cruz',
                'email' => ' BEN@EXAMPLE.TEST ',
            ],
        ],
    ));

    expect($workspace)->toBeInstanceOf(CampaignAudienceImportApprovalWorkspace::class)
        ->and($result->collection->totalRows)->toBe(2)
        ->and($result->summary->status)->toBe('ready')
        ->and($result->decision->status)->toBe('approved')
        ->and($result->decision->decidedBy)->toBe('operator-001')
        ->and($result->metadata['planning_key'])->toBe('planning-approval-workspace')
        ->and($result->metadata['audience_name'])->toBe('Approval Workspace Audience')
        ->and($result->effects)->toMatchArray([
            'approval_workspace' => true,
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

it('blocks approval for invalid imported rows while returning review context', function () {
    $result = campaignAudienceImportApprovalWorkspace()->decide('planning-approval-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-workspace-approval-002',
        audienceId: 'audience-approval-workspace',
        decision: 'approve',
        decidedBy: 'operator-002',
        rows: [
            [
                'external_reference' => 'EXT-002',
            ],
        ],
    ));

    expect($result->collection->invalidRows)->toBe(1)
        ->and($result->summary->status)->toBe('review_required')
        ->and($result->decision->status)->toBe('blocked')
        ->and($result->decision->blockers)->toBe([
            'Audience import review is not ready for approval.',
            '1 invalid row requires review.',
        ])
        ->and($result->summary->issues)->toHaveCount(1);
});

it('rejects an invalid audience import through the workspace without persistence', function () {
    $result = campaignAudienceImportApprovalWorkspace()->decide('planning-approval-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-workspace-approval-003',
        audienceId: 'audience-approval-workspace',
        decision: 'reject',
        decidedBy: 'operator-003',
        reason: 'Needs a corrected source.',
        rows: [
            [
                'external_reference' => 'EXT-003',
            ],
        ],
    ));

    expect($result->decision->status)->toBe('rejected')
        ->and($result->decision->reason)->toBe('Needs a corrected source.')
        ->and($result->summary->invalidRows)->toBe(1)
        ->and($result->effects['persists'])->toBeFalse();
});

it('fails closed for an unknown planning key before approval workspace planning', function () {
    expect(fn () => campaignAudienceImportApprovalWorkspace()->decide('missing-planning', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-workspace-approval-004',
        audienceId: 'audience-approval-workspace',
        decision: 'approve',
        rows: [
            ['name' => 'Ana Reyes', 'mobile' => '09170000001'],
        ],
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for an audience outside the stored plan before approval workspace planning', function () {
    expect(fn () => campaignAudienceImportApprovalWorkspace()->decide('planning-approval-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-workspace-approval-005',
        audienceId: 'missing-audience',
        decision: 'approve',
        rows: [
            ['name' => 'Ana Reyes', 'mobile' => '09170000001'],
        ],
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});
