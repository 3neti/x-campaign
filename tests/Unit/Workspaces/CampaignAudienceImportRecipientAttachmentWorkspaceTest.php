<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportApproval;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRowCollection;
use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportReviewSummaryReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

function campaignAudienceImportRecipientAttachmentWorkspace(): RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Attachment Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-attachment-workspace', name: 'Attachment Workspace Audience'),
    );

    $repository->put('planning-attachment-workspace', $plan);

    $approvalWorkspace = new RepositoryBackedCampaignAudienceImportApprovalWorkspace(
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

    return new RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace(
        approvalWorkspace: $approvalWorkspace,
        attachmentPlanner: new PlanCampaignAudienceImportRecipientAttachments,
    );
}

it('plans approved import recipient attachments through the workspace without mutating the audience', function () {
    $workspace = campaignAudienceImportRecipientAttachmentWorkspace();

    $result = $workspace->plan('planning-attachment-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-attachment-workspace-001',
        audienceId: 'audience-attachment-workspace',
        decision: 'approve',
        decidedBy: 'operator-001',
        reason: 'Rows reviewed.',
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

    expect($workspace)->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentWorkspace::class)
        ->and($result->approval->decision->status)->toBe('approved')
        ->and($result->attachment->status)->toBe('ready')
        ->and($result->attachment->attachableRows)->toBe(2)
        ->and($result->attachment->recipients)->toHaveCount(2)
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-attachment-workspace',
            'audience_name' => 'Attachment Workspace Audience',
            'attachment_status' => 'ready',
        ])
        ->and($result->effects)->toMatchArray([
            'attachment_workspace' => true,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('returns a blocked attachment plan when approval is blocked by invalid rows', function () {
    $result = campaignAudienceImportRecipientAttachmentWorkspace()->plan('planning-attachment-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-attachment-workspace-002',
        audienceId: 'audience-attachment-workspace',
        decision: 'approve',
        decidedBy: 'operator-002',
        rows: [
            [
                'external_reference' => 'EXT-002',
            ],
        ],
    ));

    expect($result->approval->decision->status)->toBe('blocked')
        ->and($result->approval->summary->status)->toBe('review_required')
        ->and($result->attachment->status)->toBe('blocked')
        ->and($result->attachment->attachableRows)->toBe(0)
        ->and($result->attachment->blockers)->toContain('Audience import approval is not approved.')
        ->and($result->attachment->blockers)->toContain('Audience import review is not ready for approval.')
        ->and($result->attachment->blockers)->toContain('Audience import review summary is not ready for recipient attachment.')
        ->and($result->effects['adds_recipients'])->toBeFalse();
});

it('returns a blocked attachment plan when the operator rejects the import', function () {
    $result = campaignAudienceImportRecipientAttachmentWorkspace()->plan('planning-attachment-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-attachment-workspace-003',
        audienceId: 'audience-attachment-workspace',
        decision: 'reject',
        decidedBy: 'operator-003',
        reason: 'Use a corrected source file.',
        rows: [
            [
                'name' => 'Ana Reyes',
                'mobile' => '09170000001',
            ],
        ],
    ));

    expect($result->approval->decision->status)->toBe('rejected')
        ->and($result->approval->decision->reason)->toBe('Use a corrected source file.')
        ->and($result->attachment->status)->toBe('blocked')
        ->and($result->attachment->blockers)->toBe(['Audience import approval is not approved.']);
});

it('fails closed for unknown planning keys before attachment workspace planning', function () {
    expect(fn () => campaignAudienceImportRecipientAttachmentWorkspace()->plan('missing-planning', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-attachment-workspace-004',
        audienceId: 'audience-attachment-workspace',
        decision: 'approve',
        rows: [
            ['name' => 'Ana Reyes', 'mobile' => '09170000001'],
        ],
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for audiences outside the stored plan before attachment workspace planning', function () {
    expect(fn () => campaignAudienceImportRecipientAttachmentWorkspace()->plan('planning-attachment-workspace', new CampaignAudienceImportApprovalWorkspaceInputData(
        importId: 'import-attachment-workspace-005',
        audienceId: 'missing-audience',
        decision: 'approve',
        rows: [
            ['name' => 'Ana Reyes', 'mobile' => '09170000001'],
        ],
    )))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});

