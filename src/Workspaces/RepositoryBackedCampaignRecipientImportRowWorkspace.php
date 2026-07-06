<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignRecipientImportRowWorkspace;
use LBHurtado\XCampaign\Contracts\PlansCampaignRecipientImportRows;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowPlanningInputData;

class RepositoryBackedCampaignRecipientImportRowWorkspace implements CampaignRecipientImportRowWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly PlansCampaignRecipientImportRows $planner,
    ) {}

    public function plan(string $planningKey, CampaignRecipientImportRowPlanningInputData $input): CampaignRecipientImportRowData
    {
        $plan = $this->requirePlan($planningKey);
        $audience = $this->requireAudience($plan, $input->audienceId);

        $row = $this->planner->handle($input);

        return new CampaignRecipientImportRowData(
            importId: $row->importId,
            audienceId: $row->audienceId,
            rowNumber: $row->rowNumber,
            status: $row->status,
            recipient: $row->recipient,
            raw: $row->raw,
            errors: $row->errors,
            effects: [
                ...$row->effects,
                'integrates_repository' => true,
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
                ...$row->metadata,
                'planning_key' => $planningKey,
                'audience_name' => $audience->audience->name,
                'existing_recipient_count' => count($audience->recipients),
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
