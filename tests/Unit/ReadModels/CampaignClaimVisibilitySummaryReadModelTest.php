<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignClaimVisibilitySummaries;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityResultData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilitySummaryData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;
use LBHurtado\XCampaign\ReadModels\CampaignClaimVisibilitySummaryReadModel;

it('builds a read-only claim visibility summary from a workspace result', function () {
    $result = new CampaignClaimVisibilityWorkspaceResultData(
        status: 'visible',
        planningKey: 'planning-visibility',
        executionId: 'execution-visibility',
        visibilityResults: [
            phase7fClaimVisibilityResult(status: 'visible', visibilityId: 'visibility-001', recipientId: 'recipient-001', claimStatus: 'claimed'),
            phase7fClaimVisibilityResult(status: 'visible', visibilityId: 'visibility-002', recipientId: 'recipient-002', claimStatus: 'unclaimed'),
        ],
        metadata: ['workspace_only' => true],
    );

    $summary = (new CampaignClaimVisibilitySummaryReadModel)->fromWorkspaceResult($result);

    expect(new CampaignClaimVisibilitySummaryReadModel)->toBeInstanceOf(BuildsCampaignClaimVisibilitySummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignClaimVisibilitySummaryData::class)
        ->and($summary->status)->toBe('visible')
        ->and($summary->planningKey)->toBe('planning-visibility')
        ->and($summary->executionId)->toBe('execution-visibility')
        ->and($summary->recipientCount)->toBe(2)
        ->and($summary->visibleCount)->toBe(2)
        ->and($summary->blockedCount)->toBe(0)
        ->and($summary->claimedCount)->toBe(1)
        ->and($summary->unclaimedCount)->toBe(1)
        ->and($summary->visible)->toBeTrue()
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
            'source' => 'claim-visibility-summary-read-model',
            'read_only' => true,
        ]);
});

it('summarizes blocked claim visibility workspace results without hiding blockers', function () {
    $result = new CampaignClaimVisibilityWorkspaceResultData(
        status: 'blocked',
        planningKey: 'planning-visibility',
        executionId: 'execution-visibility',
        visibilityResults: [
            phase7fClaimVisibilityResult(
                status: 'blocked',
                visibilityId: 'visibility-003',
                recipientId: 'recipient-003',
                claimStatus: 'unknown',
                blockers: ['Claim visibility requires a claim status snapshot.'],
            ),
        ],
        blockers: ['Claim visibility requires a claim status snapshot.'],
    );

    $summary = (new CampaignClaimVisibilitySummaryReadModel)->fromWorkspaceResult($result);

    expect($summary->visible)->toBeFalse()
        ->and($summary->visibleCount)->toBe(0)
        ->and($summary->blockedCount)->toBe(1)
        ->and($summary->recipientCount)->toBe(1)
        ->and($summary->blockers)->toBe(['Claim visibility requires a claim status snapshot.']);
});

function phase7fClaimVisibilityResult(
    string $status,
    string $visibilityId,
    string $recipientId,
    string $claimStatus,
    array $blockers = [],
): CampaignClaimVisibilityResultData {
    $recipient = new CampaignRecipientData(
        id: $recipientId,
        audienceId: 'audience-visibility',
        name: 'Recipient '.$recipientId,
        mobile: '+639171234567',
    );

    $execution = new CampaignExecutionData(
        id: 'execution-visibility',
        campaignId: 'campaign-visibility',
        audienceId: 'audience-visibility',
        status: 'planned',
    );

    $generationResult = new CampaignPortableCodeGenerationResultData(
        status: 'planned',
        generationId: 'generation-'.$recipientId,
        request: new CampaignPortableCodeGenerationRequestData(
            planningKey: 'planning-visibility',
            execution: $execution,
            recipient: $recipient,
        ),
        portableCodeReference: 'pay-code-'.$recipientId,
    );

    return new CampaignClaimVisibilityResultData(
        status: $status,
        visibilityId: $visibilityId,
        visibility: new CampaignClaimVisibilityData(
            planningKey: 'planning-visibility',
            execution: $execution,
            recipient: $recipient,
            generationResult: $generationResult,
            claimStatus: ['status' => $claimStatus],
            requestedBy: 'operator-001',
        ),
        blockers: $blockers,
    );
}

