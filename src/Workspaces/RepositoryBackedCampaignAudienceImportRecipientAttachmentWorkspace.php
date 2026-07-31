<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use LBHurtado\XCampaign\Contracts\CampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Data\CampaignAudienceImportApprovalWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;

class RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace implements CampaignAudienceImportRecipientAttachmentWorkspace
{
    public function __construct(
        private readonly CampaignAudienceImportApprovalWorkspace $approvalWorkspace,
        private readonly PlansCampaignAudienceImportRecipientAttachments $attachmentPlanner,
    ) {}

    public function plan(string $planningKey, CampaignAudienceImportApprovalWorkspaceInputData $input): CampaignAudienceImportRecipientAttachmentWorkspaceResultData
    {
        $approval = $this->approvalWorkspace->decide($planningKey, $input);
        $attachment = $this->attachmentPlanner->handle($approval);

        return new CampaignAudienceImportRecipientAttachmentWorkspaceResultData(
            approval: $approval,
            attachment: $attachment,
            effects: [
                ...$approval->effects,
                ...$attachment->effects,
                'attachment_workspace' => true,
                'persists' => false,
                'queues_jobs' => false,
                'adds_recipients' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$approval->metadata,
                ...$attachment->metadata,
                'planning_key' => $planningKey,
                'attachment_status' => $attachment->status,
            ],
        );
    }
}
