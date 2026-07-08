<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignOperatorReports;
use LBHurtado\XCampaign\Data\CampaignAnalyticsOperatorSummaryData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportData;
use LBHurtado\XCampaign\Data\CampaignOperatorReportRequestData;

class CampaignOperatorReportBuilder implements BuildsCampaignOperatorReports
{
    public function build(CampaignOperatorReportRequestData $request): CampaignOperatorReportData
    {
        $summary = $request->analyticsSummary;

        return new CampaignOperatorReportData(
            status: $summary->ready && $summary->blockers === [] ? 'ready' : 'attention_required',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            reportType: $request->reportType,
            format: $request->format,
            title: $this->title($request->reportType),
            sections: [
                'overview' => $this->overview($summary),
                'operator' => $this->operator($summary),
            ],
            blockers: $summary->blockers,
            metadata: [
                ...$request->metadata,
                'source' => 'campaign-operator-report-builder',
                'read_only' => true,
            ],
        );
    }

    private function title(string $reportType): string
    {
        return match ($reportType) {
            'operator_summary' => 'Campaign Operator Summary',
            default => 'Campaign Operator Report',
        };
    }

    /**
     * @return array<string, int>
     */
    private function overview(CampaignAnalyticsOperatorSummaryData $summary): array
    {
        return [
            'recipient_count' => $summary->recipientCount,
            'generated_count' => $summary->generatedCount,
            'delivery_ready_count' => $summary->deliveryReadyCount,
            'claim_visible_count' => $summary->claimVisibleCount,
            'claimed_count' => $summary->claimedCount,
        ];
    }

    /**
     * @return array<string, bool|int|string>
     */
    private function operator(CampaignAnalyticsOperatorSummaryData $summary): array
    {
        return [
            'posture' => $summary->operatorPosture,
            'ready' => $summary->ready,
            'blocker_count' => $summary->blockerCount,
        ];
    }
}
