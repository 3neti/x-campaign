<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorReadModels;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorReadModelData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationSummaryData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorReadModel;

function phaseOneVSummary(
    string $status,
    string $importId,
    int $totalRows,
    int $attachedRows,
    int $blockedRows = 0,
    int $recipientDelta = 0,
    array $blockers = [],
): CampaignAudienceImportRecipientAttachmentMutationSummaryData {
    return new CampaignAudienceImportRecipientAttachmentMutationSummaryData(
        importId: $importId,
        audienceId: 'audience-operator-read-model',
        status: $status,
        decisionStatus: $status === 'attached' ? 'allowed' : $status,
        mutationStatus: $status === 'attached' ? 'attached' : 'blocked',
        totalRows: $totalRows,
        attachableRows: max(0, $totalRows - $blockedRows),
        blockedRows: $blockedRows,
        attachedRows: $attachedRows,
        skippedRows: max(0, $totalRows - $attachedRows),
        beforeRecipientCount: 0,
        afterRecipientCount: $recipientDelta,
        recipientDelta: $recipientDelta,
        readyForMutation: $status === 'attached',
        blockers: $blockers,
        effects: [
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
        ],
        metadata: [
            'planning_key' => 'planning-operator-read-model',
        ],
    );
}

it('aggregates recipient attachment mutation summaries for operator read models', function () {
    $readModel = new CampaignAudienceImportAttachmentOperatorReadModel;

    $model = $readModel->fromMutationSummaries(
        campaignId: 'campaign-operator-read-model',
        audienceId: 'audience-operator-read-model',
        summaries: [
            phaseOneVSummary('attached', 'import-attached-001', totalRows: 2, attachedRows: 2, recipientDelta: 2),
            phaseOneVSummary('blocked', 'import-blocked-001', totalRows: 3, attachedRows: 0, blockedRows: 1, blockers: ['1 invalid row remains unresolved.']),
            phaseOneVSummary('deferred', 'import-deferred-001', totalRows: 1, attachedRows: 0, blockers: ['Recipient attachment mutation decision is not allowed.']),
        ],
        metadata: ['operator_context' => 'audience-import-attachment'],
    );

    expect($readModel)->toBeInstanceOf(BuildsCampaignAudienceImportAttachmentOperatorReadModels::class)
        ->and($model)->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorReadModelData::class)
        ->and($model->campaignId)->toBe('campaign-operator-read-model')
        ->and($model->audienceId)->toBe('audience-operator-read-model')
        ->and($model->status)->toBe('attention_required')
        ->and($model->totalImports)->toBe(3)
        ->and($model->attachedImports)->toBe(1)
        ->and($model->blockedImports)->toBe(1)
        ->and($model->deferredImports)->toBe(1)
        ->and($model->totalRows)->toBe(6)
        ->and($model->attachedRows)->toBe(2)
        ->and($model->blockedRows)->toBe(1)
        ->and($model->recipientDelta)->toBe(2)
        ->and($model->summaries)->toHaveCount(3)
        ->and($model->blockers)->toBe([
            '1 invalid row remains unresolved.',
            'Recipient attachment mutation decision is not allowed.',
        ])
        ->and($model->effects)->toMatchArray([
            'campaign_audience_import_attachment_operator_read_model' => true,
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
        ->and($model->metadata)->toMatchArray([
            'source' => 'campaign-audience-import-attachment-operator-read-model',
            'read_only' => true,
            'operator_context' => 'audience-import-attachment',
        ]);
});

it('marks all-attached operator aggregation as complete', function () {
    $model = (new CampaignAudienceImportAttachmentOperatorReadModel)->fromMutationSummaries(
        campaignId: 'campaign-complete',
        audienceId: 'audience-complete',
        summaries: [
            phaseOneVSummary('attached', 'import-attached-001', totalRows: 2, attachedRows: 2, recipientDelta: 2),
            phaseOneVSummary('attached', 'import-attached-002', totalRows: 1, attachedRows: 1, recipientDelta: 1),
        ],
    );

    expect($model->status)->toBe('complete')
        ->and($model->totalImports)->toBe(2)
        ->and($model->attachedImports)->toBe(2)
        ->and($model->blockedImports)->toBe(0)
        ->and($model->deferredImports)->toBe(0)
        ->and($model->recipientDelta)->toBe(3)
        ->and($model->blockers)->toBe([]);
});

it('marks empty operator aggregation as empty and read only', function () {
    $model = (new CampaignAudienceImportAttachmentOperatorReadModel)->fromMutationSummaries(
        campaignId: 'campaign-empty',
        audienceId: 'audience-empty',
        summaries: [],
    );

    expect($model->status)->toBe('empty')
        ->and($model->totalImports)->toBe(0)
        ->and($model->summaries)->toBe([])
        ->and($model->effects['read_only'])->toBeTrue()
        ->and($model->effects['adds_recipients'])->toBeFalse();
});
