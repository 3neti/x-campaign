<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use LBHurtado\XCampaign\Contracts\AttachesCampaignAudienceImportRecipientsInMemory;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportRecipientAttachmentMutations;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData;

class RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace implements CampaignAudienceImportRecipientAttachmentMutationWorkspace
{
    public function __construct(
        private readonly CampaignAudienceImportRecipientAttachmentWorkspace $attachmentWorkspace,
        private readonly DecidesCampaignAudienceImportRecipientAttachmentMutations $mutationDecider,
        private readonly AttachesCampaignAudienceImportRecipientsInMemory $mutator,
    ) {}

    public function attach(
        string $planningKey,
        CampaignAudienceImportApprovalWorkspaceInputData $approvalInput,
        CampaignAudienceImportRecipientAttachmentMutationDecisionInputData $mutationInput,
    ): CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData {
        $workspace = $this->attachmentWorkspace->plan($planningKey, $approvalInput);
        $decision = $this->mutationDecider->handle($workspace, $mutationInput);
        $mutation = $this->mutator->handle($planningKey, $workspace, $decision);

        return new CampaignAudienceImportRecipientAttachmentMutationWorkspaceResultData(
            workspace: $workspace,
            decision: $decision,
            mutation: $mutation,
            effects: [
                ...$workspace->effects,
                ...$decision->effects,
                ...$mutation->effects,
                'attachment_mutation_workspace' => true,
                'persists' => false,
                'uses_database' => false,
                'queues_jobs' => false,
                'adds_recipients' => $mutation->attachedRows > 0,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$workspace->metadata,
                ...$decision->metadata,
                ...$mutation->metadata,
                'planning_key' => $planningKey,
                'mutation_status' => $mutation->status,
            ],
        );
    }
}
