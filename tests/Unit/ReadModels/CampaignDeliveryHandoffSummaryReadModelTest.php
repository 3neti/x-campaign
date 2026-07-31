<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignDeliveryHandoffSummaries;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffResultData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffSummaryData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;
use LBHurtado\XCampaign\ReadModels\CampaignDeliveryHandoffSummaryReadModel;

it('builds a read-only delivery handoff summary from a workspace result', function () {
    $result = new CampaignDeliveryHandoffWorkspaceResultData(
        status: 'ready',
        planningKey: 'planning-delivery',
        executionId: 'execution-delivery',
        channel: 'sms',
        handoffResults: [
            deliveryHandoffResult(status: 'ready', handoffId: 'delivery-001', recipientId: 'recipient-001'),
            deliveryHandoffResult(status: 'ready', handoffId: 'delivery-002', recipientId: 'recipient-002'),
        ],
        metadata: ['workspace_only' => true],
    );

    $summary = (new CampaignDeliveryHandoffSummaryReadModel)->fromWorkspaceResult($result);

    expect(new CampaignDeliveryHandoffSummaryReadModel)->toBeInstanceOf(BuildsCampaignDeliveryHandoffSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignDeliveryHandoffSummaryData::class)
        ->and($summary->status)->toBe('ready')
        ->and($summary->planningKey)->toBe('planning-delivery')
        ->and($summary->executionId)->toBe('execution-delivery')
        ->and($summary->channel)->toBe('sms')
        ->and($summary->recipientCount)->toBe(2)
        ->and($summary->readyCount)->toBe(2)
        ->and($summary->blockedCount)->toBe(0)
        ->and($summary->ready)->toBeTrue()
        ->and($summary->blockers)->toBe([])
        ->and($summary->effects)->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($summary->metadata)->toMatchArray([
            'workspace_only' => true,
            'source' => 'delivery-handoff-summary-read-model',
            'read_only' => true,
        ]);
});

it('summarizes blocked delivery handoff workspace results without hiding blockers', function () {
    $result = new CampaignDeliveryHandoffWorkspaceResultData(
        status: 'blocked',
        planningKey: 'planning-delivery',
        executionId: 'execution-delivery',
        channel: 'email',
        handoffResults: [
            deliveryHandoffResult(
                status: 'blocked',
                handoffId: 'delivery-003',
                recipientId: 'recipient-003',
                blockers: ['Recipient email is required for email delivery handoff.'],
            ),
        ],
        blockers: ['Recipient email is required for email delivery handoff.'],
    );

    $summary = (new CampaignDeliveryHandoffSummaryReadModel)->fromWorkspaceResult($result);

    expect($summary->ready)->toBeFalse()
        ->and($summary->readyCount)->toBe(0)
        ->and($summary->blockedCount)->toBe(1)
        ->and($summary->recipientCount)->toBe(1)
        ->and($summary->blockers)->toBe(['Recipient email is required for email delivery handoff.']);
});

function deliveryHandoffResult(
    string $status,
    string $handoffId,
    string $recipientId,
    array $blockers = [],
): CampaignDeliveryHandoffResultData {
    $recipient = new CampaignRecipientData(
        id: $recipientId,
        audienceId: 'audience-delivery',
        name: 'Recipient '.$recipientId,
        mobile: '+639171234567',
        email: 'recipient@example.test',
    );

    $execution = new CampaignExecutionData(
        id: 'execution-delivery',
        campaignId: 'campaign-delivery',
        audienceId: 'audience-delivery',
        status: 'planned',
    );

    $generationResult = new CampaignPortableCodeGenerationResultData(
        status: 'planned',
        generationId: 'generation-'.$recipientId,
        request: new CampaignPortableCodeGenerationRequestData(
            planningKey: 'planning-delivery',
            execution: $execution,
            recipient: $recipient,
        ),
        portableCodeReference: 'pay-code-'.$recipientId,
    );

    return new CampaignDeliveryHandoffResultData(
        status: $status,
        handoffId: $handoffId,
        handoff: new CampaignDeliveryHandoffData(
            planningKey: 'planning-delivery',
            execution: $execution,
            recipient: $recipient,
            generationResult: $generationResult,
            channel: 'sms',
            requestedBy: 'operator-001',
        ),
        blockers: $blockers,
    );
}
