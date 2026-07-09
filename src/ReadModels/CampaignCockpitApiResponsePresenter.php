<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\PresentsCampaignCockpitApiResponses;
use LBHurtado\XCampaign\Data\CampaignCockpitApiResponseData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;

class CampaignCockpitApiResponsePresenter implements PresentsCampaignCockpitApiResponses
{
    public function present(CampaignCockpitSummaryData $summary): CampaignCockpitApiResponseData
    {
        return new CampaignCockpitApiResponseData(
            status: $summary->status === 'ready' ? 'ok' : $summary->status,
            data: [
                'planning_key' => $summary->planningKey,
                'execution_id' => $summary->executionId,
                'operator_id' => $summary->operatorId,
                'cards' => $summary->cards,
                'panels' => $summary->panels,
                'actions' => $summary->actions,
                'blockers' => $summary->blockers,
            ],
            meta: [
                ...$summary->metadata,
                'source' => 'campaign-cockpit-api-response-presenter',
                'read_only' => true,
                'routes_registered' => false,
                'controllers_registered' => false,
            ],
        );
    }
}
