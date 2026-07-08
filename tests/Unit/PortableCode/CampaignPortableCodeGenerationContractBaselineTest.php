<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignPortableCodeGenerations;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

it('defines a portable code generation request without generation side effects', function () {
    $request = new CampaignPortableCodeGenerationRequestData(
        planningKey: 'planning-001',
        execution: new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001'),
        recipient: new CampaignRecipientData(id: 'recipient-001', audienceId: 'audience-001', name: 'Ada'),
        batchId: 'batch-001',
        correlationId: 'corr-001',
        metadata: ['source' => 'phase-5b'],
    );

    expect($request->planningKey)->toBe('planning-001')
        ->and($request->execution->id)->toBe('execution-001')
        ->and($request->recipient->id)->toBe('recipient-001')
        ->and($request->batchId)->toBe('batch-001')
        ->and($request->correlationId)->toBe('corr-001')
        ->and($request->metadata)->toBe(['source' => 'phase-5b'])
        ->and($request->effects)->toBeInstanceOf(CampaignPersistenceEffectData::class)
        ->and($request->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a portable code generation result envelope without owning issuance truth', function () {
    $request = new CampaignPortableCodeGenerationRequestData(
        planningKey: 'planning-001',
        execution: new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001'),
        recipient: new CampaignRecipientData(id: 'recipient-001', audienceId: 'audience-001'),
    );

    $result = new CampaignPortableCodeGenerationResultData(
        status: 'planned',
        generationId: 'generation-001',
        request: $request,
        portableCodeReference: 'external-code-001',
        metadata: ['gateway' => 'null'],
    );

    expect($result->status)->toBe('planned')
        ->and($result->generationId)->toBe('generation-001')
        ->and($result->request)->toBe($request)
        ->and($result->portableCodeReference)->toBe('external-code-001')
        ->and($result->metadata)->toBe(['gateway' => 'null'])
        ->and($result->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a portable code generation planning contract before implementation exists', function () {
    expect(interface_exists(PlansCampaignPortableCodeGenerations::class))->toBeTrue()
        ->and(method_exists(PlansCampaignPortableCodeGenerations::class, 'plan'))->toBeTrue();
});
