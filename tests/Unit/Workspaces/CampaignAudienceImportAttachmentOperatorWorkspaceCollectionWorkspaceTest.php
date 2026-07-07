<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorReadModelData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;

function phaseOneYWorkspace(): RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;
    $creator = new CreateCampaignPlan;
    $audienceAdder = new AddAudienceToCampaignPlan;

    $plan = $creator->handle(new CampaignPlanningInputData(
        name: 'Operator Collection Workspace Campaign',
    ));

    $plan = $audienceAdder->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-collection-one',
        name: 'Collection Audience One',
    ));

    $plan = $audienceAdder->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-collection-two',
        name: 'Collection Audience Two',
    ));

    $repository->put('planning-collection-workspace', $plan);

    return new RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace(
        repository: $repository,
        collectionReadModel: new CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel,
    );
}

function phaseOneYWorkspaceResult(
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
            audienceId: $audienceId,
            status: $status,
            totalImports: $totalImports,
            attachedImports: $status === 'complete' ? $totalImports : 0,
            blockedImports: $status === 'attention_required' ? 1 : 0,
            totalRows: $attachedRows + $blockedRows,
            attachedRows: $attachedRows,
            blockedRows: $blockedRows,
            recipientDelta: $recipientDelta,
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
            'audience_name' => 'Audience '.$audienceId,
            'read_only' => true,
        ],
    );
}

it('builds a read-only operator workspace collection workspace from repository context', function () {
    $workspace = phaseOneYWorkspace();

    $result = $workspace->overview(
        planningKey: 'planning-collection-workspace',
        results: [
            phaseOneYWorkspaceResult('complete', 'audience-collection-one', totalImports: 1, attachedRows: 2, recipientDelta: 2),
            phaseOneYWorkspaceResult('attention_required', 'audience-collection-two', totalImports: 1, attachedRows: 0, blockedRows: 1, blockers: ['1 invalid row remains unresolved.']),
        ],
        metadata: ['operator_context' => 'workspace-collection-workspace'],
    );

    expect($workspace)->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace::class)
        ->and($result->collection->status)->toBe('attention_required')
        ->and($result->collection->planningKey)->toBe('planning-collection-workspace')
        ->and($result->collection->totalAudiences)->toBe(2)
        ->and($result->collection->completeAudiences)->toBe(1)
        ->and($result->collection->attentionRequiredAudiences)->toBe(1)
        ->and($result->collection->attachedRows)->toBe(2)
        ->and($result->collection->blockedRows)->toBe(1)
        ->and($result->collection->recipientDelta)->toBe(2)
        ->and($result->collection->blockers)->toBe(['1 invalid row remains unresolved.'])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-collection-workspace',
            'campaign_name' => 'Operator Collection Workspace Campaign',
            'operator_context' => 'workspace-collection-workspace',
            'read_only' => true,
        ])
        ->and($result->effects)->toMatchArray([
            'operator_workspace_collection_workspace' => true,
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
        ]);
});

it('returns an empty read-only collection workspace for campaigns without audience workspace results', function () {
    $result = phaseOneYWorkspace()->overview(
        planningKey: 'planning-collection-workspace',
        results: [],
    );

    expect($result->collection->status)->toBe('empty')
        ->and($result->collection->totalAudiences)->toBe(0)
        ->and($result->collection->results)->toBe([])
        ->and($result->effects['read_only'])->toBeTrue()
        ->and($result->effects['adds_recipients'])->toBeFalse();
});

it('fails closed for missing planning keys before building collection workspace state', function () {
    expect(fn () => phaseOneYWorkspace()->overview(
        planningKey: 'missing-planning',
        results: [],
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});
