<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignProductionReadinessAssessments;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessAssessmentData;
use LBHurtado\XCampaign\Data\CampaignProductionReadinessChecklistData;

class CampaignProductionReadinessAssessmentBuilder implements BuildsCampaignProductionReadinessAssessments
{
    public function build(CampaignProductionReadinessChecklistData $checklist): CampaignProductionReadinessAssessmentData
    {
        $checks = [
            'operational_readiness' => $checklist->operationalReadiness->status,
            'package_boundaries' => 'read_only',
            'host_handoff' => 'required',
        ];

        $blockers = $this->blockers($checklist);

        return new CampaignProductionReadinessAssessmentData(
            status: $blockers === [] ? 'ready' : 'blocked',
            planningKey: $checklist->planningKey,
            executionId: $checklist->executionId,
            operatorId: $checklist->operatorId,
            checks: array_intersect_key($checks, array_flip($checklist->requiredChecks)),
            blockers: $blockers,
            metadata: [
                ...$checklist->metadata,
                'source' => 'campaign-production-readiness-assessment-builder',
                'read_only' => true,
                'deploys' => false,
                'writes_environment' => false,
                'starts_workers' => false,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignProductionReadinessChecklistData $checklist): array
    {
        $readinessBlockers = $checklist->operationalReadiness->summary['blockers'] ?? [];

        if (! is_array($readinessBlockers)) {
            $readinessBlockers = [];
        }

        $blockers = array_values(array_filter($readinessBlockers, is_string(...)));

        if ($checklist->operationalReadiness->status !== 'ready') {
            $blockers[] = 'operational readiness is not ready';
        }

        return array_values(array_unique($blockers));
    }
}
