<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\PlansCampaignExportHandoffs;
use LBHurtado\XCampaign\Data\CampaignExportHandoffRequestData;
use LBHurtado\XCampaign\Data\CampaignExportHandoffResultData;

class CampaignExportHandoffPlanner implements PlansCampaignExportHandoffs
{
    public function plan(CampaignExportHandoffRequestData $request): CampaignExportHandoffResultData
    {
        $blockers = $this->blockers($request);

        return new CampaignExportHandoffResultData(
            status: $blockers === [] ? 'planned' : 'blocked',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            exportId: $this->exportId($request),
            format: $request->format,
            destination: $request->destination,
            manifest: [
                'report_type' => $request->report->reportType,
                'format' => $request->format,
                'destination' => $request->destination,
                'sections' => array_keys($request->report->sections),
                'export_generated' => false,
                'file_stored' => false,
                'delivery_sent' => false,
            ],
            blockers: $blockers,
            metadata: [
                ...$request->metadata,
                'source' => 'campaign-export-handoff-planner',
                'handoff_only' => true,
                'read_only' => true,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignExportHandoffRequestData $request): array
    {
        if ($request->report->blockers !== []) {
            return $request->report->blockers;
        }

        if ($request->report->status !== 'ready') {
            return ['operator report is not ready for export handoff'];
        }

        return [];
    }

    private function exportId(CampaignExportHandoffRequestData $request): string
    {
        return sprintf(
            'export-%s-%s-%s',
            $request->planningKey,
            $request->executionId,
            $request->format,
        );
    }
}
