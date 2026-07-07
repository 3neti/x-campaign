<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel;

function phaseOneZWorkspaceResult(
    string $status,
    int $totalAudiences,
    int $completeAudiences,
    int $attentionRequiredAudiences,
    int $emptyAudiences,
    int $totalImports,
    int $attachedRows,
    int $blockedRows,
    int $recipientDelta,
    array $blockers = [],
): CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData {
    return new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData(
        collection: new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData(
            planningKey: 'planning-operator-summary',
            status: $status,
            totalAudiences: $totalAudiences,
            completeAudiences: $completeAudiences,
            attentionRequiredAudiences: $attentionRequiredAudiences,
            emptyAudiences: $emptyAudiences,
            totalImports: $totalImports,
            attachedRows: $attachedRows,
            blockedRows: $blockedRows,
            recipientDelta: $recipientDelta,
            blockers: $blockers,
            effects: [
                'operator_workspace_collection' => true,
                'read_only' => true,
                'persists' => false,
                'uses_database' => false,
                'queues_jobs' => false,
                'adds_recipients' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                'planning_key' => 'planning-operator-summary',
                'campaign_name' => 'Operator Summary Campaign',
                'read_only' => true,
            ],
        ),
        effects: [
            'operator_workspace_collection_workspace' => true,
            'read_only' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ],
        metadata: [
            'planning_key' => 'planning-operator-summary',
            'campaign_name' => 'Operator Summary Campaign',
            'read_only' => true,
        ],
    );
}

it('builds a read-only operator summary for attention-required workspace collections', function () {
    $readModel = new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel;

    $summary = $readModel->fromWorkspaceResult(
        result: phaseOneZWorkspaceResult(
            status: 'attention_required',
            totalAudiences: 3,
            completeAudiences: 1,
            attentionRequiredAudiences: 1,
            emptyAudiences: 1,
            totalImports: 4,
            attachedRows: 5,
            blockedRows: 2,
            recipientDelta: 5,
            blockers: ['2 import rows require operator review.'],
        ),
        metadata: ['operator_context' => 'collection-operator-summary'],
    );

    expect($readModel)->toBeInstanceOf(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData::class)
        ->and($summary->planningKey)->toBe('planning-operator-summary')
        ->and($summary->campaignName)->toBe('Operator Summary Campaign')
        ->and($summary->status)->toBe('attention_required')
        ->and($summary->operatorPosture)->toBe('review_required')
        ->and($summary->totalAudiences)->toBe(3)
        ->and($summary->completeAudiences)->toBe(1)
        ->and($summary->attentionRequiredAudiences)->toBe(1)
        ->and($summary->emptyAudiences)->toBe(1)
        ->and($summary->totalImports)->toBe(4)
        ->and($summary->attachedRows)->toBe(5)
        ->and($summary->blockedRows)->toBe(2)
        ->and($summary->recipientDelta)->toBe(5)
        ->and($summary->blockerCount)->toBe(1)
        ->and($summary->blockers)->toBe(['2 import rows require operator review.'])
        ->and($summary->effects)->toMatchArray([
            'operator_workspace_collection_operator_summary' => true,
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
            'source' => 'campaign-audience-import-attachment-operator-workspace-collection-operator-summary-read-model',
            'operator_context' => 'collection-operator-summary',
            'read_only' => true,
        ]);
});

it('marks complete workspace collection summaries as ready for operator confirmation', function () {
    $summary = (new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel)->fromWorkspaceResult(
        phaseOneZWorkspaceResult(
            status: 'complete',
            totalAudiences: 2,
            completeAudiences: 2,
            attentionRequiredAudiences: 0,
            emptyAudiences: 0,
            totalImports: 2,
            attachedRows: 4,
            blockedRows: 0,
            recipientDelta: 4,
        ),
    );

    expect($summary->status)->toBe('complete')
        ->and($summary->operatorPosture)->toBe('ready_for_confirmation')
        ->and($summary->blockerCount)->toBe(0)
        ->and($summary->blockers)->toBe([]);
});

it('marks empty workspace collection summaries as no audience activity', function () {
    $summary = (new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel)->fromWorkspaceResult(
        phaseOneZWorkspaceResult(
            status: 'empty',
            totalAudiences: 0,
            completeAudiences: 0,
            attentionRequiredAudiences: 0,
            emptyAudiences: 0,
            totalImports: 0,
            attachedRows: 0,
            blockedRows: 0,
            recipientDelta: 0,
        ),
    );

    expect($summary->status)->toBe('empty')
        ->and($summary->operatorPosture)->toBe('no_audience_activity')
        ->and($summary->effects['read_only'])->toBeTrue()
        ->and($summary->effects['adds_recipients'])->toBeFalse();
});
