<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportReviewSummaries;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportApprovals;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRowCollections;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRowCollectionPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class RepositoryBackedCampaignAudienceImportApprovalWorkspace implements CampaignAudienceImportApprovalWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly PlansCampaignAudienceImportRowCollections $rowCollectionPlanner,
        private readonly BuildsCampaignAudienceImportReviewSummaries $reviewSummary,
        private readonly DecidesCampaignAudienceImportApprovals $approvalDecider,
    ) {}

    public function decide(string $planningKey, CampaignAudienceImportApprovalWorkspaceInputData $input): CampaignAudienceImportApprovalWorkspaceResultData
    {
        $plan = $this->requirePlan($planningKey);
        $audience = $this->requireAudience($plan, $input->audienceId);

        $collection = $this->rowCollectionPlanner->handle($planningKey, new CampaignAudienceImportRowCollectionPlanningInputData(
            importId: $input->importId,
            audienceId: $input->audienceId,
            startingRowNumber: $input->startingRowNumber,
            rows: $input->rows,
            metadata: $input->metadata,
        ));

        $summary = $this->reviewSummary->fromCollectionPlan($collection);
        $decision = $this->approvalDecider->handle($summary, new CampaignAudienceImportApprovalDecisionInputData(
            decision: $input->decision,
            decidedBy: $input->decidedBy,
            reason: $input->reason,
            metadata: [
                ...$input->metadata,
                'planning_key' => $planningKey,
                'audience_name' => $audience->audience->name,
            ],
        ));

        return new CampaignAudienceImportApprovalWorkspaceResultData(
            collection: $collection,
            summary: $summary,
            decision: $decision,
            effects: [
                ...$collection->effects,
                ...$summary->effects,
                ...$decision->effects,
                'approval_workspace' => true,
                'parses_files' => false,
                'persists' => false,
                'queues_jobs' => false,
                'adds_recipients' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$input->metadata,
                'planning_key' => $planningKey,
                'campaign_name' => $plan->campaign->name,
                'audience_name' => $audience->audience->name,
                'decision_status' => $decision->status,
            ],
        );
    }

    private function requirePlan(string $planningKey): CampaignPlanData
    {
        $plan = $this->repository->get($planningKey);

        if (! $plan instanceof CampaignPlanData) {
            throw new InvalidArgumentException("Unknown campaign planning key [{$planningKey}].");
        }

        return $plan;
    }

    private function requireAudience(CampaignPlanData $plan, ?string $audienceId): CampaignAudiencePlanData
    {
        $audienceId = trim((string) $audienceId);

        foreach ($plan->audiences as $audience) {
            if ($audience instanceof CampaignAudiencePlanData && $audience->audience->id === $audienceId) {
                return $audience;
            }
        }

        throw new InvalidArgumentException("Unknown campaign audience plan [{$audienceId}].");
    }
}
