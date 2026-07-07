<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\AttachCampaignAudienceImportRecipientsInMemory;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Contracts\AttachesCampaignAudienceImportRecipientsInMemory;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;

function recipientAttachmentMutationRepository(): InMemoryCampaignPlanRepository
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'In-Memory Attachment Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-memory-mutation', name: 'In-Memory Attachment Audience'),
    );

    $repository->put('planning-memory-mutation', $plan);

    return $repository;
}

function readyRecipientAttachmentWorkspaceForMutation(): CampaignAudienceImportRecipientAttachmentWorkspaceResultData
{
    return new CampaignAudienceImportRecipientAttachmentWorkspaceResultData(
        approval: new CampaignAudienceImportApprovalWorkspaceResultData(
            collection: new CampaignAudienceImportRowCollectionPlanData(
                importId: 'import-memory-mutation-001',
                audienceId: 'audience-memory-mutation',
                status: 'valid',
                totalRows: 2,
                validRows: 2,
                invalidRows: 0,
            ),
            summary: new CampaignAudienceImportReviewSummaryData(
                importId: 'import-memory-mutation-001',
                audienceId: 'audience-memory-mutation',
                status: 'ready',
                totalRows: 2,
                validRows: 2,
                invalidRows: 0,
                readyForApproval: true,
            ),
            decision: new CampaignAudienceImportApprovalDecisionData(
                importId: 'import-memory-mutation-001',
                audienceId: 'audience-memory-mutation',
                status: 'approved',
                readyForApproval: true,
            ),
            metadata: ['planning_key' => 'planning-memory-mutation'],
        ),
        attachment: new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: 'import-memory-mutation-001',
            audienceId: 'audience-memory-mutation',
            status: 'ready',
            totalRows: 2,
            attachableRows: 2,
            blockedRows: 0,
            recipients: [
                new CampaignRecipientPlanningInputData(id: 'recipient-001', name: 'Ana Reyes', mobile: '0917 000 0001'),
                new CampaignRecipientPlanningInputData(id: 'recipient-002', name: 'Ben Cruz', email: 'BEN@EXAMPLE.TEST'),
            ],
            attachableRowNumbers: [1, 2],
        ),
        metadata: [
            'planning_key' => 'planning-memory-mutation',
            'attachment_status' => 'ready',
        ],
    );
}

function allowedRecipientAttachmentMutationDecision(): CampaignAudienceImportRecipientAttachmentMutationDecisionData
{
    return new CampaignAudienceImportRecipientAttachmentMutationDecisionData(
        importId: 'import-memory-mutation-001',
        audienceId: 'audience-memory-mutation',
        decision: 'attach',
        status: 'allowed',
        decidedBy: 'operator-001',
        readyForMutation: true,
        attachableRows: 2,
        blockedRows: 0,
    );
}

it('attaches approved import recipients to the stored in-memory campaign plan', function () {
    $repository = recipientAttachmentMutationRepository();
    $action = new AttachCampaignAudienceImportRecipientsInMemory(
        repository: $repository,
        recipientAdder: new AddRecipientToCampaignAudiencePlan,
    );

    $result = $action->handle(
        'planning-memory-mutation',
        readyRecipientAttachmentWorkspaceForMutation(),
        allowedRecipientAttachmentMutationDecision(),
    );

    $stored = $repository->get('planning-memory-mutation');

    expect($action)->toBeInstanceOf(AttachesCampaignAudienceImportRecipientsInMemory::class)
        ->and($result)->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentMutationResultData::class)
        ->and($result->status)->toBe('attached')
        ->and($result->attachedRows)->toBe(2)
        ->and($result->skippedRows)->toBe(0)
        ->and($result->beforeRecipientCount)->toBe(0)
        ->and($result->afterRecipientCount)->toBe(2)
        ->and($result->plan->audiences[0]->recipients)->toHaveCount(2)
        ->and($stored?->audiences[0]->recipients)->toHaveCount(2)
        ->and($stored?->audiences[0]->recipients[0]->name)->toBe('Ana Reyes')
        ->and($stored?->audiences[0]->recipients[1]->email)->toBe('ben@example.test')
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-memory-mutation',
            'import_id' => 'import-memory-mutation-001',
            'audience_id' => 'audience-memory-mutation',
            'mutation_decision_status' => 'allowed',
        ])
        ->and($result->effects)->toMatchArray([
            'in_memory_mutation' => true,
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

it('blocks in-memory attachment when the mutation decision is not allowed', function () {
    $repository = recipientAttachmentMutationRepository();
    $action = new AttachCampaignAudienceImportRecipientsInMemory($repository, new AddRecipientToCampaignAudiencePlan);

    $result = $action->handle(
        'planning-memory-mutation',
        readyRecipientAttachmentWorkspaceForMutation(),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionData(
            importId: 'import-memory-mutation-001',
            audienceId: 'audience-memory-mutation',
            decision: 'attach',
            status: 'blocked',
            readyForMutation: false,
            attachableRows: 2,
            blockedRows: 0,
            blockers: ['Recipient attachment plan is not ready for mutation.'],
        ),
    );

    expect($result->status)->toBe('blocked')
        ->and($result->attachedRows)->toBe(0)
        ->and($result->blockers)->toBe(['Recipient attachment mutation decision is not allowed.', 'Recipient attachment plan is not ready for mutation.'])
        ->and($repository->get('planning-memory-mutation')?->audiences[0]->recipients)->toHaveCount(0);
});

it('blocks in-memory attachment when the workspace attachment plan is not ready', function () {
    $repository = recipientAttachmentMutationRepository();
    $workspaceResult = readyRecipientAttachmentWorkspaceForMutation();
    $workspaceResult = new CampaignAudienceImportRecipientAttachmentWorkspaceResultData(
        approval: $workspaceResult->approval,
        attachment: new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: 'import-memory-mutation-001',
            audienceId: 'audience-memory-mutation',
            status: 'partial',
            totalRows: 2,
            attachableRows: 1,
            blockedRows: 1,
            recipients: [new CampaignRecipientPlanningInputData(id: 'recipient-001', name: 'Ana Reyes')],
            attachableRowNumbers: [1],
            blockedRowNumbers: [2],
            blockers: ['1 row is not valid for attachment planning.'],
        ),
        metadata: $workspaceResult->metadata,
    );

    $result = (new AttachCampaignAudienceImportRecipientsInMemory($repository, new AddRecipientToCampaignAudiencePlan))->handle(
        'planning-memory-mutation',
        $workspaceResult,
        allowedRecipientAttachmentMutationDecision(),
    );

    expect($result->status)->toBe('blocked')
        ->and($result->attachedRows)->toBe(0)
        ->and($result->blockers)->toBe(['Recipient attachment workspace plan is not ready.', '1 row is not valid for attachment planning.'])
        ->and($repository->get('planning-memory-mutation')?->audiences[0]->recipients)->toHaveCount(0);
});

it('fails closed for missing campaign planning keys before in-memory attachment', function () {
    expect(fn () => (new AttachCampaignAudienceImportRecipientsInMemory(
        recipientAttachmentMutationRepository(),
        new AddRecipientToCampaignAudiencePlan,
    ))->handle(
        'missing-planning',
        readyRecipientAttachmentWorkspaceForMutation(),
        allowedRecipientAttachmentMutationDecision(),
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed when the target audience is outside the stored plan', function () {
    $repository = recipientAttachmentMutationRepository();
    $workspaceResult = readyRecipientAttachmentWorkspaceForMutation();
    $workspaceResult = new CampaignAudienceImportRecipientAttachmentWorkspaceResultData(
        approval: $workspaceResult->approval,
        attachment: new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: 'import-memory-mutation-001',
            audienceId: 'missing-audience',
            status: 'ready',
            totalRows: 1,
            attachableRows: 1,
            recipients: [new CampaignRecipientPlanningInputData(id: 'recipient-001', name: 'Ana Reyes')],
        ),
        metadata: $workspaceResult->metadata,
    );

    expect(fn () => (new AttachCampaignAudienceImportRecipientsInMemory(
        $repository,
        new AddRecipientToCampaignAudiencePlan,
    ))->handle(
        'planning-memory-mutation',
        $workspaceResult,
        allowedRecipientAttachmentMutationDecision(),
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});

