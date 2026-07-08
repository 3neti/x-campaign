<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PlansCampaignClaimVisibilities;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

it('defines a claim visibility request without owning claim lifecycle truth', function () {
    $visibility = new CampaignClaimVisibilityData(
        planningKey: 'planning-visibility',
        execution: visibilityExecution(),
        recipient: visibilityRecipient(),
        generationResult: visibilityGenerationResult(),
        claimStatus: ['status' => 'unclaimed', 'source' => 'snapshot'],
        requestedBy: 'operator-001',
        correlationId: 'corr-visibility',
        metadata: ['workspace' => 'test'],
    );

    expect($visibility->planningKey)->toBe('planning-visibility')
        ->and($visibility->execution->id)->toBe('execution-visibility')
        ->and($visibility->recipient->id)->toBe('recipient-visibility')
        ->and($visibility->generationResult->portableCodeReference)->toBe('pay-code-visibility')
        ->and($visibility->claimStatus)->toBe(['status' => 'unclaimed', 'source' => 'snapshot'])
        ->and($visibility->requestedBy)->toBe('operator-001')
        ->and($visibility->correlationId)->toBe('corr-visibility')
        ->and($visibility->effects->toArray())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a claim visibility result envelope with explicit blockers and no side effects', function () {
    $visibility = new CampaignClaimVisibilityData(
        planningKey: 'planning-visibility',
        execution: visibilityExecution(),
        recipient: visibilityRecipient(),
        generationResult: visibilityGenerationResult(),
    );

    $result = new CampaignClaimVisibilityResultData(
        status: 'visible',
        visibilityId: 'claim-visibility-001',
        visibility: $visibility,
        blockers: [],
        metadata: ['claim_runtime_invoked' => false],
    );

    expect($result->status)->toBe('visible')
        ->and($result->visibilityId)->toBe('claim-visibility-001')
        ->and($result->visibility)->toBe($visibility)
        ->and($result->metadata)->toMatchArray(['claim_runtime_invoked' => false])
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

it('defines a claim visibility planning contract', function () {
    expect(interface_exists(PlansCampaignClaimVisibilities::class))->toBeTrue()
        ->and(method_exists(PlansCampaignClaimVisibilities::class, 'plan'))->toBeTrue();
});

function visibilityExecution(): CampaignExecutionData
{
    return new CampaignExecutionData(
        id: 'execution-visibility',
        campaignId: 'campaign-visibility',
        audienceId: 'audience-visibility',
        status: 'planned',
    );
}

function visibilityRecipient(): CampaignRecipientData
{
    return new CampaignRecipientData(
        id: 'recipient-visibility',
        audienceId: 'audience-visibility',
        name: 'Recipient Visibility',
        mobile: '+639171234567',
    );
}

function visibilityGenerationResult(): CampaignPortableCodeGenerationResultData
{
    $execution = visibilityExecution();
    $recipient = visibilityRecipient();

    return new CampaignPortableCodeGenerationResultData(
        status: 'planned',
        generationId: 'generation-visibility',
        request: new CampaignPortableCodeGenerationRequestData(
            planningKey: 'planning-visibility',
            execution: $execution,
            recipient: $recipient,
        ),
        portableCodeReference: 'pay-code-visibility',
    );
}

