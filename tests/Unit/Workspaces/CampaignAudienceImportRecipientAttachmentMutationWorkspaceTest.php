<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\AttachCampaignAudienceImportRecipientsInMemory;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportApproval;
use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportRecipientAttachmentMutation;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRowCollection;
use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportReviewSummaryReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

function recipientAttachmentMutationWorkspace(): array
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Mutation Workspace Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-mutation-workspace', name: 'Mutation Workspace Audience'),
    );

    $repository->put('planning-mutation-workspace', $plan);

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

    $attachmentWorkspace = new RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace(
        approvalWorkspace: $approvalWorkspace,
        attachmentPlanner: new PlanCampaignAudienceImportRecipientAttachments,
    );

    return [
        'repository' => $repository,
        'workspace' => new RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace(
            attachmentWorkspace: $attachmentWorkspace,
            mutationDecider: new DecideCampaignAudienceImportRecipientAttachmentMutation,
            mutator: new AttachCampaignAudienceImportRecipientsInMemory(
                repository: $repository,
                recipientAdder: new AddRecipientToCampaignAudiencePlan,
            ),
        ),
    ];
}

it('runs approved import recipient attachment through the mutation workspace in memory', function () {
    ['repository' => $repository, 'workspace' => $workspace] = recipientAttachmentMutationWorkspace();

    $result = $workspace->attach(
        'planning-mutation-workspace',
        new CampaignAudienceImportApprovalWorkspaceInputData(
            importId: 'import-mutation-workspace-001',
            audienceId: 'audience-mutation-workspace',
            decision: 'approve',
            decidedBy: 'operator-001',
            rows: [
                ['full_name' => 'Ana Reyes', 'phone' => '0917 000 0001'],
                ['name' => 'Ben Cruz', 'email' => ' BEN@EXAMPLE.TEST '],
            ],
        ),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(
            decision: 'attach',
            decidedBy: 'operator-001',
            reason: 'Attach approved rows.',
        ),
    );

    expect($workspace)->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentMutationWorkspace::class)
        ->and($result->workspace->attachment->status)->toBe('ready')
        ->and($result->decision->status)->toBe('allowed')
        ->and($result->mutation->status)->toBe('attached')
        ->and($result->mutation->attachedRows)->toBe(2)
        ->and($repository->get('planning-mutation-workspace')?->audiences[0]->recipients)->toHaveCount(2)
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-mutation-workspace',
            'import_id' => 'import-mutation-workspace-001',
            'audience_id' => 'audience-mutation-workspace',
            'mutation_status' => 'attached',
        ])
        ->and($result->effects)->toMatchArray([
            'attachment_mutation_workspace' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'adds_recipients' => true,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('blocks mutation workspace attachment when import approval is blocked', function () {
    ['repository' => $repository, 'workspace' => $workspace] = recipientAttachmentMutationWorkspace();

    $result = $workspace->attach(
        'planning-mutation-workspace',
        new CampaignAudienceImportApprovalWorkspaceInputData(
            importId: 'import-mutation-workspace-002',
            audienceId: 'audience-mutation-workspace',
            decision: 'approve',
            rows: [
                ['external_reference' => 'EXT-002'],
            ],
        ),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'attach'),
    );

    expect($result->workspace->approval->decision->status)->toBe('blocked')
        ->and($result->decision->status)->toBe('blocked')
        ->and($result->mutation->status)->toBe('blocked')
        ->and($result->mutation->attachedRows)->toBe(0)
        ->and($result->mutation->blockers)->toContain('Recipient attachment mutation decision is not allowed.')
        ->and($repository->get('planning-mutation-workspace')?->audiences[0]->recipients)->toHaveCount(0);
});

it('defers mutation workspace attachment without mutating the stored plan', function () {
    ['repository' => $repository, 'workspace' => $workspace] = recipientAttachmentMutationWorkspace();

    $result = $workspace->attach(
        'planning-mutation-workspace',
        new CampaignAudienceImportApprovalWorkspaceInputData(
            importId: 'import-mutation-workspace-003',
            audienceId: 'audience-mutation-workspace',
            decision: 'approve',
            rows: [
                ['name' => 'Ana Reyes', 'mobile' => '09170000001'],
            ],
        ),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'defer'),
    );

    expect($result->decision->status)->toBe('deferred')
        ->and($result->mutation->status)->toBe('blocked')
        ->and($result->mutation->blockers)->toBe(['Recipient attachment mutation decision is not allowed.'])
        ->and($repository->get('planning-mutation-workspace')?->audiences[0]->recipients)->toHaveCount(0);
});

it('fails closed for missing planning keys before mutation workspace execution', function () {
    ['workspace' => $workspace] = recipientAttachmentMutationWorkspace();

    expect(fn () => $workspace->attach(
        'missing-planning',
        new CampaignAudienceImportApprovalWorkspaceInputData(
            importId: 'import-mutation-workspace-004',
            audienceId: 'audience-mutation-workspace',
            decision: 'approve',
            rows: [['name' => 'Ana Reyes', 'mobile' => '09170000001']],
        ),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'attach'),
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

