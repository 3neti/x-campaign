<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorReadModels;
use LBHurtado\XCampaign\Data\CampaignAudienceImportAttachmentOperatorReadModelData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportRecipientAttachmentMutationSummaryData;

class CampaignAudienceImportAttachmentOperatorReadModel implements BuildsCampaignAudienceImportAttachmentOperatorReadModels
{
    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     * @param  array<string, mixed>  $metadata
     */
    public function fromMutationSummaries(
        ?string $campaignId,
        ?string $audienceId,
        array $summaries,
        array $metadata = [],
    ): CampaignAudienceImportAttachmentOperatorReadModelData {
        $summaries = array_values(array_filter(
            $summaries,
            fn (mixed $summary): bool => $summary instanceof CampaignAudienceImportRecipientAttachmentMutationSummaryData,
        ));

        return new CampaignAudienceImportAttachmentOperatorReadModelData(
            campaignId: $campaignId,
            audienceId: $audienceId,
            status: $this->status($summaries),
            totalImports: count($summaries),
            attachedImports: $this->countByStatus($summaries, 'attached'),
            blockedImports: $this->countByStatus($summaries, 'blocked'),
            deferredImports: $this->countByStatus($summaries, 'deferred'),
            totalRows: $this->sum($summaries, 'totalRows'),
            attachedRows: $this->sum($summaries, 'attachedRows'),
            blockedRows: $this->sum($summaries, 'blockedRows'),
            recipientDelta: $this->sum($summaries, 'recipientDelta'),
            summaries: $summaries,
            blockers: $this->blockers($summaries),
            effects: [
                'campaign_audience_import_attachment_operator_read_model' => true,
                'read_only' => true,
                'persists' => false,
                'uses_database' => false,
                'queues_jobs' => false,
                'adds_recipients' => false,
                'issues_pay_codes' => false,
                'sends_feedback' => false,
                'writes_journal' => false,
                'moves_money' => false,
            ],
            metadata: [
                ...$metadata,
                'source' => 'campaign-audience-import-attachment-operator-read-model',
                'read_only' => true,
            ],
        );
    }

    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     */
    private function status(array $summaries): string
    {
        if ($summaries === []) {
            return 'empty';
        }

        if ($this->countByStatus($summaries, 'blocked') > 0 || $this->countByStatus($summaries, 'deferred') > 0) {
            return 'attention_required';
        }

        return 'complete';
    }

    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     */
    private function countByStatus(array $summaries, string $status): int
    {
        return count(array_filter(
            $summaries,
            fn (CampaignAudienceImportRecipientAttachmentMutationSummaryData $summary): bool => $summary->status === $status,
        ));
    }

    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     */
    private function sum(array $summaries, string $property): int
    {
        return array_reduce(
            $summaries,
            fn (int $total, CampaignAudienceImportRecipientAttachmentMutationSummaryData $summary): int => $total + (int) $summary->{$property},
            0,
        );
    }

    /**
     * @param  array<int, CampaignAudienceImportRecipientAttachmentMutationSummaryData>  $summaries
     * @return array<int, string>
     */
    private function blockers(array $summaries): array
    {
        return array_values(array_unique(array_merge(...array_map(
            fn (CampaignAudienceImportRecipientAttachmentMutationSummaryData $summary): array => $summary->blockers,
            $summaries,
        ))));
    }
}
