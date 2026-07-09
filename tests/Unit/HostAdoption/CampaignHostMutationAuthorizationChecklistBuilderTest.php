<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostMutationAuthorizationChecklists;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignHostMutationAuthorizationChecklistBuilder;

it('builds host mutation authorization checklists without executing mutations', function () {
    $checklist = (new CampaignHostMutationAuthorizationChecklistBuilder)->build(new CampaignXChangeIntegrationRequestData(
        planningKey: 'planning-mutation',
        executionId: 'execution-mutation',
        operatorId: 'operator-mutation',
        metadata: ['request_id' => 'request-mutation'],
    ));

    expect($checklist->status)->toBe('requires_host_decision')
        ->and($checklist->gates)->toHaveKeys([
            'operator_authorized',
            'request_validated',
            'idempotency_key_present',
            'pricing_checked',
            'funding_checked',
            'journal_handoff_available',
            'feedback_handoff_available',
        ])
        ->and($checklist->gates['operator_authorized'])->toMatchArray([
            'owner' => 'host',
            'required' => true,
            'satisfied' => false,
        ])
        ->and($checklist->blockedOperations)->toContain('pay_code_generation')
        ->and($checklist->blockedOperations)->toContain('delivery_dispatch')
        ->and($checklist->blockedOperations)->toContain('campaign_mutation_without_host_authorization')
        ->and($checklist->metadata)->toMatchArray([
            'request_id' => 'request-mutation',
            'source' => 'campaign-host-mutation-authorization-checklist-builder',
            'executes_mutations' => false,
            'host_decides' => true,
            'package_decides' => false,
        ]);
});

it('implements the host mutation authorization checklist builder contract', function () {
    expect(new CampaignHostMutationAuthorizationChecklistBuilder)
        ->toBeInstanceOf(BuildsCampaignHostMutationAuthorizationChecklists::class);
});
