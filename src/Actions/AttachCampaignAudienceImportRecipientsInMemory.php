<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\AddsRecipientsToCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\AttachesCampaignAudienceImportRecipientsInMemory;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationResultData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class AttachCampaignAudienceImportRecipientsInMemory implements AttachesCampaignAudienceImportRecipientsInMemory
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly AddsRecipientsToCampaignAudiencePlans $recipientAdder,
    ) {}

    public function handle(
        string $planningKey,
        CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspaceResult,
        CampaignAudienceImportRecipientAttachmentMutationDecisionData $decision,
    ): CampaignAudienceImportRecipientAttachmentMutationResultData {
        $plan = $this->requirePlan($planningKey);
        $audienceId = trim((string) $workspaceResult->attachment->audienceId);
        $beforeRecipientCount = $this->recipientCount($plan, $audienceId);
        $blockers = $this->blockers($workspaceResult, $decision);

        if ($blockers !== []) {
            return $this->result(
                plan: $plan,
                planningKey: $planningKey,
                workspaceResult: $workspaceResult,
                decision: $decision,
                status: 'blocked',
                attachedRows: 0,
                skippedRows: $workspaceResult->attachment->attachableRows,
                beforeRecipientCount: $beforeRecipientCount,
                afterRecipientCount: $beforeRecipientCount,
                blockers: $blockers,
            );
        }

        $mutatedPlan = $plan;

        foreach ($workspaceResult->attachment->recipients as $recipient) {
            $mutatedPlan = $this->recipientAdder->handle($mutatedPlan, $audienceId, $recipient);
        }

        $this->repository->put($planningKey, $mutatedPlan);

        return $this->result(
            plan: $mutatedPlan,
            planningKey: $planningKey,
            workspaceResult: $workspaceResult,
            decision: $decision,
            status: 'attached',
            attachedRows: count($workspaceResult->attachment->recipients),
            skippedRows: 0,
            beforeRecipientCount: $beforeRecipientCount,
            afterRecipientCount: $this->recipientCount($mutatedPlan, $audienceId),
            blockers: [],
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

    private function recipientCount(CampaignPlanData $plan, string $audienceId): int
    {
        foreach ($plan->audiences as $audience) {
            if ($audience instanceof CampaignAudiencePlanData && $audience->audience->id === $audienceId) {
                return count($audience->recipients);
            }
        }

        throw new InvalidArgumentException("Unknown campaign audience plan [{$audienceId}].");
    }

    /**
     * @return array<int, string>
     */
    private function blockers(
        CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspaceResult,
        CampaignAudienceImportRecipientAttachmentMutationDecisionData $decision,
    ): array {
        $blockers = [];

        if ($decision->status !== 'allowed' || ! $decision->readyForMutation) {
            $blockers[] = 'Recipient attachment mutation decision is not allowed.';
        }

        foreach ($decision->blockers as $blocker) {
            $blockers[] = $blocker;
        }

        if ($workspaceResult->attachment->status !== 'ready') {
            $blockers[] = 'Recipient attachment workspace plan is not ready.';
        }

        foreach ($workspaceResult->attachment->blockers as $blocker) {
            $blockers[] = $blocker;
        }

        return array_values(array_unique($blockers));
    }

    /**
     * @param  array<int, string>  $blockers
     */
    private function result(
        CampaignPlanData $plan,
        string $planningKey,
        CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspaceResult,
        CampaignAudienceImportRecipientAttachmentMutationDecisionData $decision,
        string $status,
        int $attachedRows,
        int $skippedRows,
        int $beforeRecipientCount,
        int $afterRecipientCount,
        array $blockers,
    ): CampaignAudienceImportRecipientAttachmentMutationResultData {
        return new CampaignAudienceImportRecipientAttachmentMutationResultData(
            plan: $plan,
            importId: $workspaceResult->attachment->importId,
            audienceId: $workspaceResult->attachment->audienceId,
            status: $status,
            attachedRows: $attachedRows,
            skippedRows: $skippedRows,
            beforeRecipientCount: $beforeRecipientCount,
            afterRecipientCount: $afterRecipientCount,
            blockers: $blockers,
            effects: $this->effects($attachedRows > 0),
            metadata: [
                ...$workspaceResult->metadata,
                ...$decision->metadata,
                'planning_key' => $planningKey,
                'import_id' => $workspaceResult->attachment->importId,
                'audience_id' => $workspaceResult->attachment->audienceId,
                'mutation_decision_status' => $decision->status,
            ],
        );
    }

    /**
     * @return array<string, bool>
     */
    private function effects(bool $addsRecipients): array
    {
        return [
            'in_memory_mutation' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'adds_recipients' => $addsRecipients,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }
}
