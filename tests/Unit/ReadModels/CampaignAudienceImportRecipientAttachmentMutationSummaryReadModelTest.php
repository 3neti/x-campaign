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
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportReviewSummaryReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

function phaseOneURecipientAttachmentMutationWorkspace(): RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Mutation Summary Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-mutation-summary', name: 'Mutation Summary Audience'),
    );

    $repository->put('planning-mutation-summary', $plan);

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

    return new RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace(
        attachmentWorkspace: new RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace(
            approvalWorkspace: $approvalWorkspace,
            attachmentPlanner: new PlanCampaignAudienceImportRecipientAttachments,
        ),
        mutationDecider: new DecideCampaignAudienceImportRecipientAttachmentMutation,
        mutator: new AttachCampaignAudienceImportRecipientsInMemory(
            repository: $repository,
            recipientAdder: new AddRecipientToCampaignAudiencePlan,
        ),
    );
}

it('builds a read-only summary for an approved recipient attachment mutation workspace result', function () {
    $workspace = phaseOneURecipientAttachmentMutationWorkspace();

    $result = $workspace->attach(
        'planning-mutation-summary',
        new CampaignAudienceImportApprovalWorkspaceInputData(
            importId: 'import-mutation-summary-001',
            audienceId: 'audience-mutation-summary',
            decision: 'approve',
            rows: [
                ['full_name' => 'Ana Reyes', 'phone' => '0917 000 0001'],
                ['name' => 'Ben Cruz', 'email' => 'ben@example.test'],
            ],
        ),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(
            decision: 'attach',
            decidedBy: 'operator-001',
            reason: 'Approved import review.',
        ),
    );

    $summary = (new CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel)->fromWorkspaceResult($result);

    expect(new CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel)->toBeInstanceOf(BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentMutationSummaryData::class)
        ->and($summary->importId)->toBe('import-mutation-summary-001')
        ->and($summary->audienceId)->toBe('audience-mutation-summary')
        ->and($summary->status)->toBe('attached')
        ->and($summary->decisionStatus)->toBe('allowed')
        ->and($summary->mutationStatus)->toBe('attached')
        ->and($summary->totalRows)->toBe(2)
        ->and($summary->attachableRows)->toBe(2)
        ->and($summary->attachedRows)->toBe(2)
        ->and($summary->skippedRows)->toBe(0)
        ->and($summary->beforeRecipientCount)->toBe(0)
        ->and($summary->afterRecipientCount)->toBe(2)
        ->and($summary->recipientDelta)->toBe(2)
        ->and($summary->readyForMutation)->toBeTrue()
        ->and($summary->blockers)->toBe([])
        ->and($summary->effects)->toMatchArray([
            'recipient_attachment_mutation_summary' => true,
            'read_only' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($summary->metadata)->toMatchArray([
            'source' => 'campaign-audience-import-recipient-attachment-mutation-summary-read-model',
            'read_only' => true,
            'planning_key' => 'planning-mutation-summary',
        ]);
});

it('summarizes blocked mutation workspace results without mutating or hiding blockers', function () {
    $workspace = phaseOneURecipientAttachmentMutationWorkspace();

    $result = $workspace->attach(
        'planning-mutation-summary',
        new CampaignAudienceImportApprovalWorkspaceInputData(
            importId: 'import-mutation-summary-002',
            audienceId: 'audience-mutation-summary',
            decision: 'approve',
            rows: [
                ['external_reference' => 'EXT-002'],
            ],
        ),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'attach'),
    );

    $summary = (new CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel)->fromWorkspaceResult($result);

    expect($summary->status)->toBe('blocked')
        ->and($summary->decisionStatus)->toBe('blocked')
        ->and($summary->mutationStatus)->toBe('blocked')
        ->and($summary->totalRows)->toBe(1)
        ->and($summary->attachableRows)->toBe(0)
        ->and($summary->blockedRows)->toBe(1)
        ->and($summary->attachedRows)->toBe(0)
        ->and($summary->recipientDelta)->toBe(0)
        ->and($summary->readyForMutation)->toBeFalse()
        ->and($summary->blockers)->toContain('Audience import review summary is not ready for recipient attachment.')
        ->and($summary->blockers)->toContain('1 invalid row remains unresolved.')
        ->and($summary->blockers)->toContain('Recipient attachment mutation decision is not allowed.');
});

it('summarizes deferred mutation workspace results as deferred read-only state', function () {
    $workspace = phaseOneURecipientAttachmentMutationWorkspace();

    $result = $workspace->attach(
        'planning-mutation-summary',
        new CampaignAudienceImportApprovalWorkspaceInputData(
            importId: 'import-mutation-summary-003',
            audienceId: 'audience-mutation-summary',
            decision: 'approve',
            rows: [
                ['name' => 'Ana Reyes', 'mobile' => '09170000001'],
            ],
        ),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'defer'),
    );

    $summary = (new CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel)->fromWorkspaceResult($result);

    expect($summary->status)->toBe('deferred')
        ->and($summary->decisionStatus)->toBe('deferred')
        ->and($summary->mutationStatus)->toBe('blocked')
        ->and($summary->totalRows)->toBe(1)
        ->and($summary->attachableRows)->toBe(1)
        ->and($summary->attachedRows)->toBe(0)
        ->and($summary->recipientDelta)->toBe(0)
        ->and($summary->readyForMutation)->toBeFalse()
        ->and($summary->blockers)->toBe(['Recipient attachment mutation decision is not allowed.']);
});
