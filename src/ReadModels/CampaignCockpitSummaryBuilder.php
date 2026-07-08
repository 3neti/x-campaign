<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitSummaries;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryRequestData;

class CampaignCockpitSummaryBuilder implements BuildsCampaignCockpitSummaries
{
    public function build(CampaignCockpitSummaryRequestData $request): CampaignCockpitSummaryData
    {
        $blockers = $this->blockers($request);

        return new CampaignCockpitSummaryData(
            status: $blockers === [] ? 'ready' : 'attention_required',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            operatorId: $request->operatorId,
            cards: [
                'campaign' => [
                    'campaign_id' => $request->campaignSummary->campaignId,
                    'name' => $request->campaignSummary->name,
                    'status' => $request->campaignSummary->status,
                    'recipient_count' => $request->campaignSummary->recipientCount,
                ],
                'analytics' => [
                    'operator_posture' => $request->analyticsSummary->operatorPosture,
                    'generated_count' => $request->analyticsSummary->generatedCount,
                    'delivery_ready_count' => $request->analyticsSummary->deliveryReadyCount,
                    'claim_visible_count' => $request->analyticsSummary->claimVisibleCount,
                    'claimed_count' => $request->analyticsSummary->claimedCount,
                ],
                'export' => [
                    'status' => $request->exportHandoff->status,
                    'format' => $request->exportHandoff->format,
                    'destination' => $request->exportHandoff->destination,
                ],
            ],
            panels: [
                'report' => [
                    'title' => $request->operatorReport->title,
                    'sections' => $request->operatorReport->sections,
                ],
            ],
            actions: [
                'refresh' => [
                    'available' => true,
                    'method' => 'read',
                ],
                'export' => [
                    'available' => $request->exportHandoff->status === 'planned' && $request->exportHandoff->blockers === [],
                    'method' => 'handoff',
                ],
            ],
            blockers: $blockers,
            metadata: [
                ...$request->metadata,
                'operator_id' => $request->operatorId,
                'source' => 'campaign-cockpit-summary-builder',
                'read_only' => true,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignCockpitSummaryRequestData $request): array
    {
        return array_values(array_unique([
            ...$request->analyticsSummary->blockers,
            ...$request->operatorReport->blockers,
            ...$request->exportHandoff->blockers,
        ]));
    }
}
