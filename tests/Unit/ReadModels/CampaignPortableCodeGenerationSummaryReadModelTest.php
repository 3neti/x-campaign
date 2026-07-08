<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPortableCodeGenerationSummaries;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationSummaryData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;
use LBHurtado\XCampaign\ReadModels\CampaignPortableCodeGenerationSummaryReadModel;

it('builds a read-only portable code generation summary from a workspace result', function () {
    $workspaceResult = new CampaignPortableCodeGenerationWorkspaceResultData(
        status: 'planned',
        planningKey: 'planning-001',
        executionId: 'execution-001',
        generationResults: [
            portableCodeGenerationResult('generation-001', 'recipient-001'),
            portableCodeGenerationResult('generation-002', 'recipient-002'),
        ],
        metadata: [
            'campaign_id' => 'campaign-001',
            'audience_id' => 'audience-001',
            'gateway_invoked' => false,
        ],
    );

    $summary = (new CampaignPortableCodeGenerationSummaryReadModel)->fromWorkspaceResult($workspaceResult);

    expect(new CampaignPortableCodeGenerationSummaryReadModel)
        ->toBeInstanceOf(BuildsCampaignPortableCodeGenerationSummaries::class)
        ->and($summary)->toBeInstanceOf(CampaignPortableCodeGenerationSummaryData::class)
        ->and($summary->status)->toBe('planned')
        ->and($summary->planningKey)->toBe('planning-001')
        ->and($summary->executionId)->toBe('execution-001')
        ->and($summary->plannedCount)->toBe(2)
        ->and($summary->blockedCount)->toBe(0)
        ->and($summary->recipientCount)->toBe(2)
        ->and($summary->ready)->toBeTrue()
        ->and($summary->blockers)->toBe([])
        ->and($summary->effects)->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ])
        ->and($summary->metadata)->toMatchArray([
            'source' => 'portable-code-generation-summary-read-model',
            'campaign_id' => 'campaign-001',
            'audience_id' => 'audience-001',
            'gateway_invoked' => false,
            'read_only' => true,
        ]);
});

it('summarizes blocked portable code generation workspace results without hiding blockers', function () {
    $workspaceResult = new CampaignPortableCodeGenerationWorkspaceResultData(
        status: 'blocked',
        planningKey: 'planning-002',
        executionId: 'execution-002',
        blockers: ['Portable code generation requires at least one planned recipient.'],
        metadata: [
            'recipient_count' => 0,
            'generation_count' => 0,
            'gateway_invoked' => false,
        ],
    );

    $summary = (new CampaignPortableCodeGenerationSummaryReadModel)->fromWorkspaceResult($workspaceResult);

    expect($summary->ready)->toBeFalse()
        ->and($summary->plannedCount)->toBe(0)
        ->and($summary->blockedCount)->toBe(0)
        ->and($summary->recipientCount)->toBe(0)
        ->and($summary->blockers)->toBe(['Portable code generation requires at least one planned recipient.']);
});

function portableCodeGenerationResult(string $generationId, string $recipientId): CampaignPortableCodeGenerationResultData
{
    return new CampaignPortableCodeGenerationResultData(
        status: 'planned',
        generationId: $generationId,
        request: new CampaignPortableCodeGenerationRequestData(
            planningKey: 'planning-001',
            execution: new CampaignExecutionData(
                id: 'execution-001',
                campaignId: 'campaign-001',
                audienceId: 'audience-001',
                status: 'planned',
            ),
            recipient: new CampaignRecipientData(id: $recipientId),
        ),
    );
}

