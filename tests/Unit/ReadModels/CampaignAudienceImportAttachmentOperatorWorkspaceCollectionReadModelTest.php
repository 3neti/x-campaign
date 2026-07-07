<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorReadModelData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel;

function phaseOneXWorkspaceResult(
    string $status,
    string $audienceId,
    int $totalImports,
    int $attachedRows,
    int $blockedRows = 0,
    int $recipientDelta = 0,
    array $blockers = [],
): CampaignAudienceImportAttachmentOperatorWorkspaceResultData {
    return new CampaignAudienceImportAttachmentOperatorWorkspaceResultData(
        overview: new CampaignAudienceImportAttachmentOperatorReadModelData(
            campaignId: null,
            audienceId: $audienceId,
            status: $status,
            totalImports: $totalImports,
            attachedImports: $status === 'complete' ? $totalImports : 0,
            blockedImports: $status === 'attention_required' ? 1 : 0,
            deferredImports: 0,
            totalRows: $attachedRows + $blockedRows,
            attachedRows: $attachedRows,
            blockedRows: $blockedRows,
            recipientDelta: $recipientDelta,
            summaries: [],
            blockers: $blockers,
            effects: [
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
        ),
        effects: [
            'operator_workspace' => true,
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
            'planning_key' => 'planning-collection',
            'audience_name' => 'Audience '.$audienceId,
            'read_only' => true,
        ],
    );
}

it('aggregates operator workspace results into a read-only collection', function () {
    $readModel = new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel;

    $collection = $readModel->fromWorkspaceResults(
        planningKey: 'planning-collection',
        results: [
            phaseOneXWorkspaceResult('complete', 'audience-complete', totalImports: 2, attachedRows: 3, recipientDelta: 3),
            phaseOneXWorkspaceResult('attention_required', 'audience-attention', totalImports: 1, attachedRows: 0, blockedRows: 1, blockers: ['1 invalid row remains unresolved.']),
            phaseOneXWorkspaceResult('empty', 'audience-empty', totalImports: 0, attachedRows: 0),
        ],
        metadata: ['operator_context' => 'workspace-collection'],
    );

    expect($readModel)->toBeInstanceOf(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections::class)
        ->and($collection)->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData::class)
        ->and($collection->planningKey)->toBe('planning-collection')
        ->and($collection->status)->toBe('attention_required')
        ->and($collection->totalAudiences)->toBe(3)
        ->and($collection->completeAudiences)->toBe(1)
        ->and($collection->attentionRequiredAudiences)->toBe(1)
        ->and($collection->emptyAudiences)->toBe(1)
        ->and($collection->totalImports)->toBe(3)
        ->and($collection->attachedRows)->toBe(3)
        ->and($collection->blockedRows)->toBe(1)
        ->and($collection->recipientDelta)->toBe(3)
        ->and($collection->results)->toHaveCount(3)
        ->and($collection->blockers)->toBe(['1 invalid row remains unresolved.'])
        ->and($collection->effects)->toMatchArray([
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
        ])
        ->and($collection->metadata)->toMatchArray([
            'source' => 'campaign-audience-import-attachment-operator-workspace-collection-read-model',
            'operator_context' => 'workspace-collection',
            'read_only' => true,
        ]);
});

it('marks all complete workspace collections as complete', function () {
    $collection = (new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel)->fromWorkspaceResults(
        planningKey: 'planning-complete',
        results: [
            phaseOneXWorkspaceResult('complete', 'audience-one', totalImports: 1, attachedRows: 2, recipientDelta: 2),
            phaseOneXWorkspaceResult('complete', 'audience-two', totalImports: 1, attachedRows: 1, recipientDelta: 1),
        ],
    );

    expect($collection->status)->toBe('complete')
        ->and($collection->completeAudiences)->toBe(2)
        ->and($collection->attentionRequiredAudiences)->toBe(0)
        ->and($collection->emptyAudiences)->toBe(0)
        ->and($collection->recipientDelta)->toBe(3)
        ->and($collection->blockers)->toBe([]);
});

it('marks empty workspace collections as empty and read only', function () {
    $collection = (new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel)->fromWorkspaceResults(
        planningKey: 'planning-empty',
        results: [],
    );

    expect($collection->status)->toBe('empty')
        ->and($collection->totalAudiences)->toBe(0)
        ->and($collection->results)->toBe([])
        ->and($collection->effects['read_only'])->toBeTrue()
        ->and($collection->effects['adds_recipients'])->toBeFalse();
});
