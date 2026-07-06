<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImport;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImports;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanningInputData;

it('plans an audience import intent without parsing files or persisting records', function () {
    $planned = (new PlanCampaignAudienceImport)->handle(new CampaignAudienceImportPlanningInputData(
        id: 'import-001',
        audienceId: 'audience-001',
        source: 'csv',
        sourceReference: 'beneficiaries-q1.csv',
        expectedRecipientCount: 150,
        columns: ['name', 'mobile', 'email'],
        metadata: ['operator' => 'campaign-ops'],
    ));

    expect(new PlanCampaignAudienceImport)->toBeInstanceOf(PlansCampaignAudienceImports::class)
        ->and($planned)->toBeInstanceOf(CampaignAudienceImportPlanData::class)
        ->and($planned->import->id)->toBe('import-001')
        ->and($planned->import->audienceId)->toBe('audience-001')
        ->and($planned->import->source)->toBe('csv')
        ->and($planned->import->status)->toBe('planned')
        ->and($planned->import->recipientCount)->toBe(150)
        ->and($planned->metadata['source_reference'])->toBe('beneficiaries-q1.csv')
        ->and($planned->metadata['columns'])->toBe(['name', 'mobile', 'email'])
        ->and($planned->effects)->toMatchArray([
            'parses_files' => false,
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defaults missing import identifiers and source metadata to manual planning semantics', function () {
    $planned = (new PlanCampaignAudienceImport)->handle(new CampaignAudienceImportPlanningInputData(
        audienceId: 'audience-manual',
    ));

    expect($planned->import->id)->toBeNull()
        ->and($planned->import->audienceId)->toBe('audience-manual')
        ->and($planned->import->source)->toBe('manual')
        ->and($planned->import->status)->toBe('planned')
        ->and($planned->import->recipientCount)->toBe(0)
        ->and($planned->metadata['source_reference'])->toBeNull()
        ->and($planned->metadata['columns'])->toBe([]);
});

it('rejects empty audience identifiers before planning an import', function () {
    expect(fn () => (new PlanCampaignAudienceImport)->handle(new CampaignAudienceImportPlanningInputData(
        audienceId: ' ',
    )))->toThrow(InvalidArgumentException::class, 'Campaign audience import planning requires an audience id.');
});

