<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignDeliveryHandoffs;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

it('defines a delivery handoff DTO without delivery side effects', function () {
    $execution = new CampaignExecutionData(
        id: 'execution-001',
        campaignId: 'campaign-001',
        audienceId: 'audience-001',
        status: 'planned',
        correlationId: 'corr-001',
    );

    $recipient = new CampaignRecipientData(id: 'recipient-001', mobile: '+639171111111');
    $generationResult = campaignDeliveryHandoffGenerationResult($execution, $recipient);

    $handoff = new CampaignDeliveryHandoffData(
        planningKey: 'planning-001',
        execution: $execution,
        recipient: $recipient,
        generationResult: $generationResult,
        channel: 'sms',
        requestedBy: 'queue',
        correlationId: 'corr-001',
        metadata: ['operation' => 'delivery.handoff'],
    );

    expect($handoff->planningKey)->toBe('planning-001')
        ->and($handoff->execution)->toBe($execution)
        ->and($handoff->recipient)->toBe($recipient)
        ->and($handoff->generationResult)->toBe($generationResult)
        ->and($handoff->channel)->toBe('sms')
        ->and($handoff->requestedBy)->toBe('queue')
        ->and($handoff->correlationId)->toBe('corr-001')
        ->and($handoff->metadata)->toBe(['operation' => 'delivery.handoff'])
        ->and($handoff->effects)->toBeInstanceOf(CampaignPersistenceEffectData::class)
        ->and($handoff->effects->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a delivery handoff result envelope that remains handoff-only by default', function () {
    $execution = new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001');
    $recipient = new CampaignRecipientData(id: 'recipient-001', mobile: '+639171111111');
    $handoff = new CampaignDeliveryHandoffData(
        planningKey: 'planning-001',
        execution: $execution,
        recipient: $recipient,
        generationResult: campaignDeliveryHandoffGenerationResult($execution, $recipient),
        channel: 'sms',
        correlationId: 'corr-001',
    );

    $result = new CampaignDeliveryHandoffResultData(
        status: 'ready',
        handoffId: 'delivery-handoff-001',
        handoff: $handoff,
        blockers: [],
        metadata: ['source' => 'phase-6b'],
    );

    expect($result->status)->toBe('ready')
        ->and($result->handoffId)->toBe('delivery-handoff-001')
        ->and($result->handoff)->toBe($handoff)
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toBe(['source' => 'phase-6b'])
        ->and($result->effects->toArray())->toMatchArray([
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a delivery handoff planning contract before delivery handoff implementation exists', function () {
    expect(interface_exists(PlansCampaignDeliveryHandoffs::class))->toBeTrue()
        ->and(method_exists(PlansCampaignDeliveryHandoffs::class, 'plan'))->toBeTrue();
});

function campaignDeliveryHandoffGenerationResult(
    CampaignExecutionData $execution,
    CampaignRecipientData $recipient,
): CampaignPortableCodeGenerationResultData {
    return new CampaignPortableCodeGenerationResultData(
        status: 'planned',
        generationId: 'generation-001',
        request: new CampaignPortableCodeGenerationRequestData(
            planningKey: 'planning-001',
            execution: $execution,
            recipient: $recipient,
        ),
        portableCodeReference: 'portable-code-001',
    );
}
