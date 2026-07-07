<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

function approvedAudienceImportWorkspaceResult(): CampaignAudienceImportApprovalWorkspaceResultData
{
    $rows = [
        new CampaignRecipientImportRowData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            rowNumber: 1,
            status: 'valid',
            recipient: new CampaignRecipientPlanningInputData(
                id: 'recipient-001',
                name: 'Ada Lovelace',
                mobile: '+639171111111',
                email: 'ada@example.test',
                externalReference: 'ROW-001',
                metadata: ['segment' => 'alpha'],
            ),
        ),
        new CampaignRecipientImportRowData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            rowNumber: 2,
            status: 'valid',
            recipient: new CampaignRecipientPlanningInputData(
                id: 'recipient-002',
                name: 'Grace Hopper',
                mobile: '+639172222222',
                email: 'grace@example.test',
                externalReference: 'ROW-002',
            ),
        ),
    ];

    return new CampaignAudienceImportApprovalWorkspaceResultData(
        collection: new CampaignAudienceImportRowCollectionPlanData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            status: 'valid',
            totalRows: 2,
            validRows: 2,
            invalidRows: 0,
            rows: $rows,
        ),
        summary: new CampaignAudienceImportReviewSummaryData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            status: 'ready',
            totalRows: 2,
            validRows: 2,
            invalidRows: 0,
            readyForApproval: true,
            validRowNumbers: [1, 2],
        ),
        decision: new CampaignAudienceImportApprovalDecisionData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            decision: 'approve',
            status: 'approved',
            decidedBy: 'operator-001',
            reason: 'Rows reviewed.',
            readyForApproval: true,
        ),
        metadata: [
            'planning_key' => 'planning-attachment',
            'audience_name' => 'Attachment Audience',
        ],
    );
}

it('plans recipient attachments from an approved audience import without mutating the campaign audience', function () {
    $plan = (new PlanCampaignAudienceImportRecipientAttachments)->handle(approvedAudienceImportWorkspaceResult());

    expect(new PlanCampaignAudienceImportRecipientAttachments)->toBeInstanceOf(PlansCampaignAudienceImportRecipientAttachments::class)
        ->and($plan)->toBeInstanceOf(CampaignAudienceImportRecipientAttachmentPlanData::class)
        ->and($plan->importId)->toBe('import-attachment-001')
        ->and($plan->audienceId)->toBe('audience-attachment')
        ->and($plan->status)->toBe('ready')
        ->and($plan->totalRows)->toBe(2)
        ->and($plan->attachableRows)->toBe(2)
        ->and($plan->blockedRows)->toBe(0)
        ->and($plan->attachableRowNumbers)->toBe([1, 2])
        ->and($plan->blockedRowNumbers)->toBe([])
        ->and($plan->recipients)->toHaveCount(2)
        ->and($plan->recipients[0])->toBeInstanceOf(CampaignRecipientPlanningInputData::class)
        ->and($plan->recipients[0]->name)->toBe('Ada Lovelace')
        ->and($plan->recipients[0]->metadata)->toMatchArray([
            'segment' => 'alpha',
            'import_id' => 'import-attachment-001',
            'audience_id' => 'audience-attachment',
            'source_row_number' => 1,
        ])
        ->and($plan->metadata)->toMatchArray([
            'planning_key' => 'planning-attachment',
            'approval_status' => 'approved',
            'summary_status' => 'ready',
        ])
        ->and($plan->effects)->toMatchArray([
            'attachment_plan' => true,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('blocks attachment planning when approval decision is not approved', function () {
    $workspaceResult = approvedAudienceImportWorkspaceResult();
    $workspaceResult = new CampaignAudienceImportApprovalWorkspaceResultData(
        collection: $workspaceResult->collection,
        summary: $workspaceResult->summary,
        decision: new CampaignAudienceImportApprovalDecisionData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            decision: 'approve',
            status: 'blocked',
            readyForApproval: false,
            blockers: ['Audience import review is not ready for approval.'],
        ),
        metadata: $workspaceResult->metadata,
    );

    $plan = (new PlanCampaignAudienceImportRecipientAttachments)->handle($workspaceResult);

    expect($plan->status)->toBe('blocked')
        ->and($plan->attachableRows)->toBe(0)
        ->and($plan->blockedRows)->toBe(2)
        ->and($plan->attachableRowNumbers)->toBe([])
        ->and($plan->blockedRowNumbers)->toBe([1, 2])
        ->and($plan->blockers)->toContain('Audience import approval is not approved.')
        ->and($plan->blockers)->toContain('Audience import review is not ready for approval.');
});

it('blocks attachment planning when the review summary is not ready', function () {
    $workspaceResult = approvedAudienceImportWorkspaceResult();
    $workspaceResult = new CampaignAudienceImportApprovalWorkspaceResultData(
        collection: $workspaceResult->collection,
        summary: new CampaignAudienceImportReviewSummaryData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            status: 'review_required',
            totalRows: 2,
            validRows: 1,
            invalidRows: 1,
            readyForApproval: false,
            validRowNumbers: [1],
            invalidRowNumbers: [2],
        ),
        decision: $workspaceResult->decision,
        metadata: $workspaceResult->metadata,
    );

    $plan = (new PlanCampaignAudienceImportRecipientAttachments)->handle($workspaceResult);

    expect($plan->status)->toBe('blocked')
        ->and($plan->attachableRows)->toBe(0)
        ->and($plan->blockedRows)->toBe(2)
        ->and($plan->blockers)->toContain('Audience import review summary is not ready for recipient attachment.')
        ->and($plan->blockers)->toContain('1 invalid row remains unresolved.');
});

it('skips non-valid rows even when attachment planning is otherwise approved', function () {
    $workspaceResult = approvedAudienceImportWorkspaceResult();
    $rows = [
        $workspaceResult->collection->rows[0],
        new CampaignRecipientImportRowData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            rowNumber: 2,
            status: 'invalid',
            recipient: new CampaignRecipientPlanningInputData(name: null),
            errors: ['name' => 'Recipient name is required.'],
        ),
    ];

    $workspaceResult = new CampaignAudienceImportApprovalWorkspaceResultData(
        collection: new CampaignAudienceImportRowCollectionPlanData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            status: 'mixed',
            totalRows: 2,
            validRows: 1,
            invalidRows: 1,
            rows: $rows,
        ),
        summary: new CampaignAudienceImportReviewSummaryData(
            importId: 'import-attachment-001',
            audienceId: 'audience-attachment',
            status: 'ready',
            totalRows: 2,
            validRows: 2,
            invalidRows: 0,
            readyForApproval: true,
            validRowNumbers: [1, 2],
        ),
        decision: $workspaceResult->decision,
        metadata: $workspaceResult->metadata,
    );

    $plan = (new PlanCampaignAudienceImportRecipientAttachments)->handle($workspaceResult);

    expect($plan->status)->toBe('partial')
        ->and($plan->attachableRows)->toBe(1)
        ->and($plan->blockedRows)->toBe(1)
        ->and($plan->attachableRowNumbers)->toBe([1])
        ->and($plan->blockedRowNumbers)->toBe([2])
        ->and($plan->blockers)->toBe(['1 row is not valid for attachment planning.']);
});

