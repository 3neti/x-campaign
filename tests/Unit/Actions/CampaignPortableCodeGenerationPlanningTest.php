<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignPortableCodeGeneration;
use LBHurtado\XCampaign\Contracts\PlansCampaignPortableCodeGenerations;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

it('plans portable code generation without invoking issuance gateways', function () {
    $request = new CampaignPortableCodeGenerationRequestData(
        planningKey: 'planning-001',
        execution: new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001'),
        recipient: new CampaignRecipientData(id: 'recipient-001', audienceId: 'audience-001'),
        batchId: 'batch-001',
        correlationId: 'corr-001',
    );

    $result = (new PlanCampaignPortableCodeGeneration)->plan($request);

    expect(new PlanCampaignPortableCodeGeneration)->toBeInstanceOf(PlansCampaignPortableCodeGenerations::class)
        ->and($result->status)->toBe('planned')
        ->and($result->generationId)->toBe('generation-'.substr(hash('sha256', 'planning-001|execution-001|recipient-001'), 0, 16))
        ->and($result->request)->toBe($request)
        ->and($result->portableCodeReference)->toBeNull()
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-001',
            'campaign_id' => 'campaign-001',
            'audience_id' => 'audience-001',
            'execution_id' => 'execution-001',
            'recipient_id' => 'recipient-001',
            'batch_id' => 'batch-001',
            'correlation_id' => 'corr-001',
            'source' => 'portable-code-generation-planner',
            'gateway_invoked' => false,
        ])
        ->and($result->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('fails closed before planning portable code generation without a planning key', function () {
    expect(fn () => (new PlanCampaignPortableCodeGeneration)->plan(new CampaignPortableCodeGenerationRequestData(
        planningKey: '',
        execution: new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001'),
        recipient: new CampaignRecipientData(id: 'recipient-001', audienceId: 'audience-001'),
    )))->toThrow(InvalidArgumentException::class, 'Portable code generation planning key is required.');
});

it('blocks portable code generation planning without a recipient identifier', function () {
    $result = (new PlanCampaignPortableCodeGeneration)->plan(new CampaignPortableCodeGenerationRequestData(
        planningKey: 'planning-001',
        execution: new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001'),
        recipient: new CampaignRecipientData(audienceId: 'audience-001'),
    ));

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toBe(['Portable code generation requires a recipient identifier.']);
});
