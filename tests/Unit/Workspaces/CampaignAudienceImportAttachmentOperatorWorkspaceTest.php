<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspace;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationSummaryData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace;

function phaseOneWOperatorWorkspace(): RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace
{
    $repository = new InMemoryCampaignPlanRepository;

    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
            name: 'Operator Workspace Campaign',
        )),
        new CampaignAudiencePlanningInputData(
            id: 'audience-operator-workspace',
            name: 'Operator Workspace Audience',
        ),
    );

    $repository->put('planning-operator-workspace', $plan);

    return new RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace(
        repository: $repository,
        readModel: new CampaignAudienceImportAttachmentOperatorReadModel,
    );
}

function phaseOneWMutationSummary(string $status, string $importId): CampaignAudienceImportRecipientAttachmentMutationSummaryData
{
    return new CampaignAudienceImportRecipientAttachmentMutationSummaryData(
        importId: $importId,
        audienceId: 'audience-operator-workspace',
        status: $status,
        decisionStatus: $status === 'attached' ? 'allowed' : $status,
        mutationStatus: $status === 'attached' ? 'attached' : 'blocked',
        totalRows: 2,
        attachableRows: $status === 'blocked' ? 1 : 2,
        blockedRows: $status === 'blocked' ? 1 : 0,
        attachedRows: $status === 'attached' ? 2 : 0,
        skippedRows: $status === 'attached' ? 0 : 2,
        beforeRecipientCount: 0,
        afterRecipientCount: $status === 'attached' ? 2 : 0,
        recipientDelta: $status === 'attached' ? 2 : 0,
        readyForMutation: $status === 'attached',
        blockers: $status === 'blocked' ? ['1 invalid row remains unresolved.'] : [],
    );
}

it('builds an operator attachment workspace from repository context and mutation summaries', function () {
    $workspace = phaseOneWOperatorWorkspace();

    $result = $workspace->overview(
        planningKey: 'planning-operator-workspace',
        audienceId: 'audience-operator-workspace',
        summaries: [
            phaseOneWMutationSummary('attached', 'import-attached-001'),
            phaseOneWMutationSummary('blocked', 'import-blocked-001'),
        ],
        metadata: ['operator_context' => 'workspace'],
    );

    expect($workspace)->toBeInstanceOf(CampaignAudienceImportAttachmentOperatorWorkspace::class)
        ->and($result->overview->status)->toBe('attention_required')
        ->and($result->overview->campaignId)->toBeNull()
        ->and($result->overview->audienceId)->toBe('audience-operator-workspace')
        ->and($result->overview->totalImports)->toBe(2)
        ->and($result->overview->attachedImports)->toBe(1)
        ->and($result->overview->blockedImports)->toBe(1)
        ->and($result->overview->recipientDelta)->toBe(2)
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-operator-workspace',
            'campaign_name' => 'Operator Workspace Campaign',
            'audience_name' => 'Operator Workspace Audience',
            'operator_context' => 'workspace',
            'read_only' => true,
        ])
        ->and($result->effects)->toMatchArray([
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
        ]);
});

it('returns an empty operator workspace overview for an audience without import summaries', function () {
    $result = phaseOneWOperatorWorkspace()->overview(
        planningKey: 'planning-operator-workspace',
        audienceId: 'audience-operator-workspace',
        summaries: [],
    );

    expect($result->overview->status)->toBe('empty')
        ->and($result->overview->totalImports)->toBe(0)
        ->and($result->overview->summaries)->toBe([])
        ->and($result->effects['read_only'])->toBeTrue()
        ->and($result->effects['adds_recipients'])->toBeFalse();
});

it('fails closed for missing planning keys before building an operator workspace overview', function () {
    expect(fn () => phaseOneWOperatorWorkspace()->overview(
        planningKey: 'missing-planning',
        audienceId: 'audience-operator-workspace',
        summaries: [],
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-planning].');
});

it('fails closed for audiences outside the stored campaign plan', function () {
    expect(fn () => phaseOneWOperatorWorkspace()->overview(
        planningKey: 'planning-operator-workspace',
        audienceId: 'missing-audience',
        summaries: [],
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});
