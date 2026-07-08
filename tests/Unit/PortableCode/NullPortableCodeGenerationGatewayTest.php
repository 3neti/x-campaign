<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PayCodeGenerationGateway;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;
use LBHurtado\XCampaign\Gateways\NullPortableCodeGenerationGateway;

it('provides a null portable code generation gateway without issuing external codes', function () {
    $result = (new NullPortableCodeGenerationGateway)->generate(
        new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001'),
        new CampaignRecipientData(id: 'recipient-001', audienceId: 'audience-001', name: 'Ada'),
    );

    expect(new NullPortableCodeGenerationGateway)->toBeInstanceOf(PayCodeGenerationGateway::class)
        ->and($result)->toMatchArray([
            'status' => 'planned',
            'generation_id' => 'generation-'.substr(hash('sha256', 'execution-001|recipient-001'), 0, 16),
            'portable_code_reference' => null,
            'provider_reference' => null,
            'metadata' => [
                'gateway' => 'null',
                'issued' => false,
                'handoff_only' => true,
            ],
            'effects' => [
                'persists' => false,
                'uses_database' => false,
                'queues_jobs' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
        ]);
});

it('keeps null gateway identifiers stable for identical execution recipient pairs', function () {
    $gateway = new NullPortableCodeGenerationGateway;
    $execution = new CampaignExecutionData(id: 'execution-001', campaignId: 'campaign-001', audienceId: 'audience-001');
    $recipient = new CampaignRecipientData(id: 'recipient-001', audienceId: 'audience-001');

    expect($gateway->generate($execution, $recipient)['generation_id'])
        ->toBe($gateway->generate($execution, $recipient)['generation_id']);
});
