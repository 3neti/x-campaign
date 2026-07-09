<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostMutationAuthorizationChecklists;
use LBHurtado\XCampaign\Data\CampaignHostMutationAuthorizationChecklistData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

class CampaignHostMutationAuthorizationChecklistBuilder implements BuildsCampaignHostMutationAuthorizationChecklists
{
    public function build(CampaignXChangeIntegrationRequestData $request): CampaignHostMutationAuthorizationChecklistData
    {
        return new CampaignHostMutationAuthorizationChecklistData(
            status: 'requires_host_decision',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            operatorId: $request->operatorId,
            gates: [
                'operator_authorized' => $this->hostGate(),
                'request_validated' => $this->hostGate(),
                'idempotency_key_present' => $this->hostGate(),
                'pricing_checked' => $this->hostGate(),
                'funding_checked' => $this->hostGate(),
                'journal_handoff_available' => $this->hostGate(),
                'feedback_handoff_available' => $this->hostGate(),
            ],
            blockedOperations: [
                'pay_code_generation',
                'delivery_dispatch',
                'campaign_mutation_without_host_authorization',
                'journal_write_without_host_handoff',
                'feedback_send_without_host_handoff',
            ],
            hostResponsibilities: [
                'authorization',
                'request_validation',
                'idempotency',
                'pricing',
                'funding',
                'journal_handoff',
                'feedback_handoff',
            ],
            packageResponsibilities: [
                'checklist_shape',
                'gate_descriptions',
                'blocked_operation_descriptions',
                'effect_metadata',
            ],
            metadata: [
                ...$request->metadata,
                'source' => 'campaign-host-mutation-authorization-checklist-builder',
                'executes_mutations' => false,
                'host_decides' => true,
                'package_decides' => false,
            ],
            effects: $request->effects,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function hostGate(): array
    {
        return [
            'owner' => 'host',
            'required' => true,
            'satisfied' => false,
        ];
    }
}
