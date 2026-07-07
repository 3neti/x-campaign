<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportRecipientAttachmentMutation;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportRecipientAttachmentMutations;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

function readyRecipientAttachmentWorkspaceResult(): CampaignAudienceImportRecipientAttachmentWorkspaceResultData
{
    return new CampaignAudienceImportRecipientAttachmentWorkspaceResultData(
        approval: new CampaignAudienceImportApprovalWorkspaceResultData(
            collection: new CampaignAudienceImportRowCollectionPlanData(
                importId: 'import-mutation-001',
                audienceId: 'audience-mutation',
                status: 'valid',
                totalRows: 2,
                validRows: 2,
                invalidRows: 0,
            ),
            summary: new CampaignAudienceImportReviewSummaryData(
                importId: 'import-mutation-001',
                audienceId: 'audience-mutation',
                status: 'ready',
                totalRows: 2,
                validRows: 2,
                invalidRows: 0,
                readyForApproval: true,
            ),
            decision: new CampaignAudienceImportApprovalDecisionData(
                importId: 'import-mutation-001',
                audienceId: 'audience-mutation',
                decision: 'approve',
                status: 'approved',
                decidedBy: 'operator-001',
                readyForApproval: true,
            ),
            metadata: ['planning_key' => 'planning-mutation'],
        ),
        attachment: new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: 'import-mutation-001',
            audienceId: 'audience-mutation',
            status: 'ready',
            totalRows: 2,
            attachableRows: 2,
            blockedRows: 0,
            recipients: [
                new CampaignRecipientPlanningInputData(id: 'recipient-001', name: 'Ana Reyes'),
                new CampaignRecipientPlanningInputData(id: 'recipient-002', name: 'Ben Cruz'),
            ],
            attachableRowNumbers: [1, 2],
            blockedRowNumbers: [],
        ),
        metadata: [
            'planning_key' => 'planning-mutation',
            'attachment_status' => 'ready',
        ],
    );
}

it('allows recipient attachment mutation only as a decision when the attachment plan is ready', function () {
    $decision = (new DecideCampaignAudienceImportRecipientAttachmentMutation)->handle(
        readyRecipientAttachmentWorkspaceResult(),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(
            decision: 'attach',
            decidedBy: 'operator-001',
            reason: 'Approved import can be attached.',
        ),
    );

    expect(new DecideCampaignAudienceImportRecipientAttachmentMutation)->toBeInstanceOf(DecidesCampaignAudienceImportRecipientAttachmentMutations::class)
        ->and($decision)->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentMutationDecisionData::class)
        ->and($decision->importId)->toBe('import-mutation-001')
        ->and($decision->audienceId)->toBe('audience-mutation')
        ->and($decision->decision)->toBe('attach')
        ->and($decision->status)->toBe('allowed')
        ->and($decision->readyForMutation)->toBeTrue()
        ->and($decision->attachableRows)->toBe(2)
        ->and($decision->blockedRows)->toBe(0)
        ->and($decision->blockers)->toBe([])
        ->and($decision->metadata)->toMatchArray([
            'planning_key' => 'planning-mutation',
            'attachment_status' => 'ready',
        ])
        ->and($decision->effects)->toMatchArray([
            'mutation_decision_only' => true,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defers a ready attachment plan without allowing mutation', function () {
    $decision = (new DecideCampaignAudienceImportRecipientAttachmentMutation)->handle(
        readyRecipientAttachmentWorkspaceResult(),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(
            decision: 'defer',
            decidedBy: 'operator-002',
            reason: 'Wait for a later operator window.',
        ),
    );

    expect($decision->status)->toBe('deferred')
        ->and($decision->readyForMutation)->toBeFalse()
        ->and($decision->blockers)->toBe([])
        ->and($decision->reason)->toBe('Wait for a later operator window.');
});

it('blocks mutation when the attachment plan is not ready', function () {
    $workspaceResult = readyRecipientAttachmentWorkspaceResult();
    $workspaceResult = new CampaignAudienceImportRecipientAttachmentWorkspaceResultData(
        approval: $workspaceResult->approval,
        attachment: new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: 'import-mutation-001',
            audienceId: 'audience-mutation',
            status: 'blocked',
            totalRows: 2,
            attachableRows: 0,
            blockedRows: 2,
            blockedRowNumbers: [1, 2],
            blockers: ['Audience import approval is not approved.'],
        ),
        metadata: $workspaceResult->metadata,
    );

    $decision = (new DecideCampaignAudienceImportRecipientAttachmentMutation)->handle(
        $workspaceResult,
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'attach'),
    );

    expect($decision->status)->toBe('blocked')
        ->and($decision->readyForMutation)->toBeFalse()
        ->and($decision->attachableRows)->toBe(0)
        ->and($decision->blockedRows)->toBe(2)
        ->and($decision->blockers)->toContain('Recipient attachment plan is not ready for mutation.')
        ->and($decision->blockers)->toContain('Audience import approval is not approved.');
});

it('blocks partial attachment plans before mutation', function () {
    $workspaceResult = readyRecipientAttachmentWorkspaceResult();
    $workspaceResult = new CampaignAudienceImportRecipientAttachmentWorkspaceResultData(
        approval: $workspaceResult->approval,
        attachment: new CampaignAudienceImportRecipientAttachmentPlanData(
            importId: 'import-mutation-001',
            audienceId: 'audience-mutation',
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

    $decision = (new DecideCampaignAudienceImportRecipientAttachmentMutation)->handle(
        $workspaceResult,
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'attach'),
    );

    expect($decision->status)->toBe('blocked')
        ->and($decision->readyForMutation)->toBeFalse()
        ->and($decision->blockers)->toContain('Recipient attachment plan is not ready for mutation.')
        ->and($decision->blockers)->toContain('1 blocked row remains unresolved.');
});

it('fails closed for unknown mutation decisions', function () {
    $decision = (new DecideCampaignAudienceImportRecipientAttachmentMutation)->handle(
        readyRecipientAttachmentWorkspaceResult(),
        new CampaignAudienceImportRecipientAttachmentMutationDecisionInputData(decision: 'ship'),
    );

    expect($decision->status)->toBe('blocked')
        ->and($decision->readyForMutation)->toBeFalse()
        ->and($decision->blockers)->toBe(['Unknown recipient attachment mutation decision [ship].']);
});

