<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignClaimVisibility;
use LBHurtado\XCampaign\Contracts\PlansCampaignClaimVisibilities;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

it('plans visible claim visibility from portable-code and claim status snapshots', function () {
    $visibility = phase7cVisibility(claimStatus: ['status' => 'claimed']);

    $result = (new PlanCampaignClaimVisibility)->plan($visibility);

    expect(new PlanCampaignClaimVisibility)->toBeInstanceOf(PlansCampaignClaimVisibilities::class)
        ->and($result->status)->toBe('visible')
        ->and($result->visibilityId)->toStartWith('claim-visibility-')
        ->and($result->blockers)->toBe([])
        ->and($result->metadata)->toMatchArray([
            'planning_key' => 'planning-visibility',
            'campaign_id' => 'campaign-visibility',
            'audience_id' => 'audience-visibility',
            'execution_id' => 'execution-visibility',
            'recipient_id' => 'recipient-visibility',
            'generation_id' => 'generation-visibility',
            'portable_code_reference' => 'pay-code-visibility',
            'claim_status' => 'claimed',
            'claim_runtime_invoked' => false,
            'source' => 'in-memory-claim-visibility-planning',
        ])
        ->and($result->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('blocks claim visibility planning without a portable-code reference', function () {
    $result = (new PlanCampaignClaimVisibility)->plan(phase7cVisibility(portableCodeReference: null));

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toBe(['Claim visibility requires a portable-code reference.']);
});

it('blocks claim visibility planning when claim status snapshot is missing', function () {
    $result = (new PlanCampaignClaimVisibility)->plan(phase7cVisibility(claimStatus: []));

    expect($result->status)->toBe('blocked')
        ->and($result->blockers)->toBe(['Claim visibility requires a claim status snapshot.']);
});

it('fails closed when the planning key is empty', function () {
    expect(fn () => (new PlanCampaignClaimVisibility)->plan(phase7cVisibility(planningKey: '')))
        ->toThrow(InvalidArgumentException::class, 'Claim visibility planning key is required.');
});

function phase7cVisibility(
    string $planningKey = 'planning-visibility',
    ?string $portableCodeReference = 'pay-code-visibility',
    array $claimStatus = ['status' => 'unclaimed'],
): CampaignClaimVisibilityData {
    $execution = new CampaignExecutionData(
        id: 'execution-visibility',
        campaignId: 'campaign-visibility',
        audienceId: 'audience-visibility',
        status: 'planned',
    );

    $recipient = new CampaignRecipientData(
        id: 'recipient-visibility',
        audienceId: 'audience-visibility',
        name: 'Recipient Visibility',
        mobile: '+639171234567',
    );

    return new CampaignClaimVisibilityData(
        planningKey: $planningKey,
        execution: $execution,
        recipient: $recipient,
        generationResult: new CampaignPortableCodeGenerationResultData(
            status: 'planned',
            generationId: 'generation-visibility',
            request: new CampaignPortableCodeGenerationRequestData(
                planningKey: $planningKey,
                execution: $execution,
                recipient: $recipient,
            ),
            portableCodeReference: $portableCodeReference,
        ),
        claimStatus: $claimStatus,
        requestedBy: 'operator-001',
        correlationId: 'corr-visibility',
    );
}

