<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportApproval;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportApprovals;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewRowIssueData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportReviewSummaryData;

it('approves a ready audience import review without attaching recipients or dispatching work', function () {
    $summary = new CampaignAudienceImportReviewSummaryData(
        importId: 'import-approval-001',
        audienceId: 'audience-approval',
        status: 'ready',
        totalRows: 2,
        validRows: 2,
        invalidRows: 0,
        readyForApproval: true,
        validRowNumbers: [1, 2],
        invalidRowNumbers: [],
    );

    $decision = (new DecideCampaignAudienceImportApproval)->handle($summary, new CampaignAudienceImportApprovalDecisionInputData(
        decision: 'approve',
        decidedBy: 'operator-001',
        reason: 'Rows reviewed.',
    ));

    expect(new DecideCampaignAudienceImportApproval)->toBeInstanceOf(DecidesCampaignAudienceImportApprovals::class)
        ->and($decision)->toBeInstanceOf(CampaignAudienceImportApprovalDecisionData::class)
        ->and($decision->importId)->toBe('import-approval-001')
        ->and($decision->audienceId)->toBe('audience-approval')
        ->and($decision->decision)->toBe('approve')
        ->and($decision->status)->toBe('approved')
        ->and($decision->decidedBy)->toBe('operator-001')
        ->and($decision->reason)->toBe('Rows reviewed.')
        ->and($decision->readyForApproval)->toBeTrue()
        ->and($decision->blockers)->toBe([])
        ->and($decision->metadata['summary_status'])->toBe('ready')
        ->and($decision->effects)->toMatchArray([
            'decision_only' => true,
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

it('rejects an audience import review as a decision without mutating import state', function () {
    $summary = new CampaignAudienceImportReviewSummaryData(
        importId: 'import-approval-002',
        audienceId: 'audience-approval',
        status: 'review_required',
        totalRows: 2,
        validRows: 1,
        invalidRows: 1,
        readyForApproval: false,
        validRowNumbers: [1],
        invalidRowNumbers: [2],
        issues: [
            new CampaignAudienceImportReviewRowIssueData(
                rowNumber: 2,
                errors: ['name' => 'Recipient name is required.'],
            ),
        ],
    );

    $decision = (new DecideCampaignAudienceImportApproval)->handle($summary, new CampaignAudienceImportApprovalDecisionInputData(
        decision: 'reject',
        decidedBy: 'operator-002',
        reason: 'Invalid rows need correction.',
    ));

    expect($decision->decision)->toBe('reject')
        ->and($decision->status)->toBe('rejected')
        ->and($decision->readyForApproval)->toBeFalse()
        ->and($decision->blockers)->toBe([])
        ->and($decision->metadata['issue_count'])->toBe(1);
});

it('blocks approval when review summary is not ready for approval', function () {
    $summary = new CampaignAudienceImportReviewSummaryData(
        importId: 'import-approval-003',
        audienceId: 'audience-approval',
        status: 'review_required',
        totalRows: 3,
        validRows: 2,
        invalidRows: 1,
        readyForApproval: false,
        validRowNumbers: [1, 2],
        invalidRowNumbers: [3],
        issues: [
            new CampaignAudienceImportReviewRowIssueData(
                rowNumber: 3,
                errors: ['contact' => 'At least one recipient contact field is required.'],
            ),
        ],
    );

    $decision = (new DecideCampaignAudienceImportApproval)->handle($summary, new CampaignAudienceImportApprovalDecisionInputData(
        decision: 'approve',
        decidedBy: 'operator-003',
    ));

    expect($decision->decision)->toBe('approve')
        ->and($decision->status)->toBe('blocked')
        ->and($decision->readyForApproval)->toBeFalse()
        ->and($decision->blockers)->toBe([
            'Audience import review is not ready for approval.',
            '1 invalid row requires review.',
        ])
        ->and($decision->metadata['invalid_row_numbers'])->toBe([3]);
});

it('normalizes unknown approval decisions to blocked fail-closed decisions', function () {
    $summary = new CampaignAudienceImportReviewSummaryData(
        importId: 'import-approval-004',
        audienceId: 'audience-approval',
        status: 'ready',
        totalRows: 1,
        validRows: 1,
        readyForApproval: true,
    );

    $decision = (new DecideCampaignAudienceImportApproval)->handle($summary, new CampaignAudienceImportApprovalDecisionInputData(
        decision: 'ship',
        decidedBy: 'operator-004',
    ));

    expect($decision->decision)->toBe('ship')
        ->and($decision->status)->toBe('blocked')
        ->and($decision->blockers)->toBe(['Unknown audience import approval decision [ship].']);
});
