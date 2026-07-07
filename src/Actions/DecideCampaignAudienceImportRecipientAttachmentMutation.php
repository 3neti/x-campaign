<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportRecipientAttachmentMutations;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationDecisionInputData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentWorkspaceResultData;

class DecideCampaignAudienceImportRecipientAttachmentMutation implements DecidesCampaignAudienceImportRecipientAttachmentMutations
{
    public function handle(
        CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspaceResult,
        CampaignAudienceImportRecipientAttachmentMutationDecisionInputData $input,
    ): CampaignAudienceImportRecipientAttachmentMutationDecisionData {
        $decision = $this->normalizedDecision($input->decision);
        $blockers = $this->blockers($workspaceResult, $decision, $input->decision);
        $status = $this->status($decision, $blockers);

        return new CampaignAudienceImportRecipientAttachmentMutationDecisionData(
            importId: $workspaceResult->attachment->importId,
            audienceId: $workspaceResult->attachment->audienceId,
            decision: $this->nullableString($input->decision) ?? '',
            status: $status,
            decidedBy: $this->nullableString($input->decidedBy),
            reason: $this->nullableString($input->reason),
            readyForMutation: $status === 'allowed',
            attachableRows: $workspaceResult->attachment->attachableRows,
            blockedRows: $workspaceResult->attachment->blockedRows,
            blockers: $blockers,
            effects: $this->effects(),
            metadata: [
                ...$workspaceResult->metadata,
                ...$input->metadata,
                'attachment_status' => $workspaceResult->attachment->status,
                'attachable_row_numbers' => $workspaceResult->attachment->attachableRowNumbers,
                'blocked_row_numbers' => $workspaceResult->attachment->blockedRowNumbers,
            ],
        );
    }

    private function normalizedDecision(string $decision): ?string
    {
        $decision = strtolower(trim($decision));

        return in_array($decision, ['attach', 'defer'], true) ? $decision : null;
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignAudienceImportRecipientAttachmentWorkspaceResultData $workspaceResult, ?string $decision, string $rawDecision): array
    {
        if ($decision === null) {
            return ['Unknown recipient attachment mutation decision ['.trim($rawDecision).'].'];
        }

        if ($decision === 'defer') {
            return [];
        }

        $blockers = [];

        if ($workspaceResult->attachment->status !== 'ready') {
            $blockers[] = 'Recipient attachment plan is not ready for mutation.';
        }

        foreach ($workspaceResult->attachment->blockers as $blocker) {
            $blockers[] = $blocker;
        }

        if ($workspaceResult->attachment->blockedRows > 0) {
            $blockers[] = $workspaceResult->attachment->blockedRows === 1
                ? '1 blocked row remains unresolved.'
                : $workspaceResult->attachment->blockedRows.' blocked rows remain unresolved.';
        }

        if ($workspaceResult->attachment->attachableRows === 0) {
            $blockers[] = 'No recipients are ready for attachment mutation.';
        }

        return array_values(array_unique($blockers));
    }

    /**
     * @param  array<int, string>  $blockers
     */
    private function status(?string $decision, array $blockers): string
    {
        if ($blockers !== []) {
            return 'blocked';
        }

        return $decision === 'defer' ? 'deferred' : 'allowed';
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'mutation_decision_only' => true,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }
}

