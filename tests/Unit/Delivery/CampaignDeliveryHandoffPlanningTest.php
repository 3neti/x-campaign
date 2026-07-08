<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignDeliveryHandoff;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

it('plans a delivery handoff in memory without sending feedback', function () {
    $execution = new CampaignExecutionData(
        id: 'execution-delivery',
        campaignId: 'campaign-001',
        audienceId: 'audience-001',
        status: 'planned',
        correlationId: 'corr-delivery',
    );

    $recipient = new CampaignRecipientData(id: 'recipient-001', mobile: '+639171111111');

    $result = (new PlanCampaignDeliveryHandoff)->plan(new CampaignDeliveryHandoffData(
        planningKey: 'planning-delivery',
        execution: $execution,
        recipient: $recipient,
        generationResult: deliveryGenerationResult($execution, $recipient, portableCodeReference: 'portable-code-001'),
        channel: 'sms',
        requestedBy: 'operator',
        correlationId: 'corr-delivery',
    ));

    expect($result->status)->toBe('ready')
        ->and($result->handoffId)->toStartWith('delivery-handoff-')
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-delivery',
            'execution_id' => 'execution-delivery',
            'recipient_id' => 'recipient-001',
            'generation_id' => 'generation-001',
            'channel' => 'sms',
            'requested_by' => 'operator',
            'handoff_only' => true,
            'delivery_invoked' => false,
        ])
        ->and($result->effects->toArray())->toMatchArray([
            'sends_feedback' => false,
            'writes_journal' => false,
            'issues_pay_codes' => false,
            'moves_money' => false,
        ]);
});

it('blocks delivery handoff planning until portable code generation is planned', function () {
    $execution = new CampaignExecutionData(id: 'execution-delivery', campaignId: 'campaign-001', audienceId: 'audience-001');
    $recipient = new CampaignRecipientData(id: 'recipient-001', mobile: '+639171111111');

    $result = (new PlanCampaignDeliveryHandoff)->plan(new CampaignDeliveryHandoffData(
        planningKey: 'planning-delivery',
        execution: $execution,
        recipient: $recipient,
        generationResult: deliveryGenerationResult($execution, $recipient, status: 'blocked'),
        channel: 'sms',
    ));

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toContain('Delivery handoff requires planned portable-code generation.');
});

it('blocks delivery handoff planning without a recipient contact for the selected channel', function () {
    $execution = new CampaignExecutionData(id: 'execution-delivery', campaignId: 'campaign-001', audienceId: 'audience-001');
    $recipient = new CampaignRecipientData(id: 'recipient-001');

    $result = (new PlanCampaignDeliveryHandoff)->plan(new CampaignDeliveryHandoffData(
        planningKey: 'planning-delivery',
        execution: $execution,
        recipient: $recipient,
        generationResult: deliveryGenerationResult($execution, $recipient, portableCodeReference: 'portable-code-001'),
        channel: 'sms',
    ));

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toContain('Delivery handoff requires a recipient mobile number for sms channel.');
});

it('fails closed before planning delivery handoff without a planning key', function () {
    $execution = new CampaignExecutionData(id: 'execution-delivery', campaignId: 'campaign-001', audienceId: 'audience-001');
    $recipient = new CampaignRecipientData(id: 'recipient-001', mobile: '+639171111111');

    expect(fn () => (new PlanCampaignDeliveryHandoff)->plan(new CampaignDeliveryHandoffData(
        planningKey: ' ',
        execution: $execution,
        recipient: $recipient,
        generationResult: deliveryGenerationResult($execution, $recipient, portableCodeReference: 'portable-code-001'),
        channel: 'sms',
    )))->toThrow(InvalidArgumentException::class, 'Delivery handoff planning key is required.');
});

function deliveryGenerationResult(
    CampaignExecutionData $execution,
    CampaignRecipientData $recipient,
    string $status = 'planned',
    ?string $portableCodeReference = null,
): CampaignPortableCodeGenerationResultData {
    return new CampaignPortableCodeGenerationResultData(
        status: $status,
        generationId: 'generation-001',
        request: new CampaignPortableCodeGenerationRequestData(
            planningKey: 'planning-delivery',
            execution: $execution,
            recipient: $recipient,
        ),
        portableCodeReference: $portableCodeReference,
    );
}

