<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\PresentsCampaignPublicApiResponses;
use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiResponseData;

class CampaignPublicApiResponsePresenter implements PresentsCampaignPublicApiResponses
{
    public function present(CampaignPublicApiDescriptorData $descriptor): CampaignPublicApiResponseData
    {
        return new CampaignPublicApiResponseData(
            status: $descriptor->status === 'described' ? 'ok' : $descriptor->status,
            data: [
                'planning_key' => $descriptor->planningKey,
                'execution_id' => $descriptor->executionId,
                'operator_id' => $descriptor->operatorId,
                'api_version' => $descriptor->apiVersion,
                'descriptor_status' => $descriptor->status,
                'endpoints' => $descriptor->endpoints,
                'host_responsibilities' => $descriptor->hostResponsibilities,
                'package_responsibilities' => $descriptor->packageResponsibilities,
            ],
            meta: [
                ...$descriptor->metadata,
                'source' => 'campaign-public-api-response-presenter',
                'read_only' => true,
                'registers_routes' => false,
                'registers_controllers' => false,
                'owns_requests' => false,
                'owns_resources' => false,
            ],
            effects: $descriptor->effects,
        );
    }
}
