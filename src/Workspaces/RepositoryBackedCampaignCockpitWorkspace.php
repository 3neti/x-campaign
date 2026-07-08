<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsOperatorSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignOperatorReports;
use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Contracts\CampaignAnalyticsWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignCockpitWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\PlansCampaignExportHandoffs;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryRequestData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffRequestData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportRequestData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class RepositoryBackedCampaignCockpitWorkspace implements CampaignCockpitWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly BuildsCampaignSummaries $campaignSummaries,
        private readonly CampaignAnalyticsWorkspace $analyticsWorkspace,
        private readonly BuildsCampaignAnalyticsOperatorSummaries $analyticsSummaries,
        private readonly BuildsCampaignOperatorReports $operatorReports,
        private readonly PlansCampaignExportHandoffs $exportHandoffs,
        private readonly BuildsCampaignCockpitSummaries $cockpitSummaries,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function summary(
        string $planningKey,
        string $executionId,
        string $operatorId,
        string $channel = 'sms',
        ?string $correlationId = null,
        array $metadata = [],
    ): CampaignCockpitSummaryData {
        $plan = $this->requirePlan($planningKey);
        $workspaceMetadata = [
            ...$metadata,
            'workspace' => 'repository-backed',
            'cockpit_only' => true,
        ];

        $analyticsSnapshot = $this->analyticsWorkspace->snapshot(
            planningKey: $planningKey,
            executionId: $executionId,
            channel: $channel,
            correlationId: $correlationId,
            metadata: $workspaceMetadata,
        );
        $analyticsSummary = $this->analyticsSummaries->fromSnapshot($analyticsSnapshot, $workspaceMetadata);
        $operatorReport = $this->operatorReports->build(new CampaignOperatorReportRequestData(
            planningKey: $planningKey,
            executionId: $executionId,
            reportType: 'operator_summary',
            format: 'array',
            analyticsSummary: $analyticsSummary,
            metadata: $workspaceMetadata,
        ));
        $exportHandoff = $this->exportHandoffs->plan(new CampaignExportHandoffRequestData(
            planningKey: $planningKey,
            executionId: $executionId,
            format: 'csv',
            destination: 'operator_download',
            report: $operatorReport,
            metadata: $workspaceMetadata,
        ));

        return $this->cockpitSummaries->build(new CampaignCockpitSummaryRequestData(
            planningKey: $planningKey,
            executionId: $executionId,
            operatorId: $operatorId,
            campaignSummary: $this->campaignSummaries->fromPlan($plan),
            analyticsSummary: $analyticsSummary,
            operatorReport: $operatorReport,
            exportHandoff: $exportHandoff,
            metadata: $workspaceMetadata,
            effects: new CampaignPersistenceEffectData,
        ));
    }

    /**
     * @return array<string, bool>
     */
    public function effects(): array
    {
        return [
            ...$this->repository->effects(),
            'integrates_repository' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }

    private function requirePlan(string $planningKey): CampaignPlanData
    {
        $plan = $this->repository->get($planningKey);

        if (! $plan instanceof CampaignPlanData) {
            throw new InvalidArgumentException("Unknown campaign planning key [{$planningKey}].");
        }

        return $plan;
    }
}
