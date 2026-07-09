<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitConsumptionMaps;
use LBHurtado\XCampaign\Data\CampaignCockpitConsumptionMapData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

class CampaignCockpitConsumptionMapBuilder implements BuildsCampaignCockpitConsumptionMaps
{
    public function build(CampaignXChangeIntegrationRequestData $request): CampaignCockpitConsumptionMapData
    {
        return new CampaignCockpitConsumptionMapData(
            status: 'described',
            planningKey: $request->planningKey,
            executionId: $request->executionId,
            operatorId: $request->operatorId,
            surfaces: [
                'dashboard' => [
                    'workspace' => 'CampaignCockpitWorkspace',
                    'read_only' => true,
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                ],
                'campaign_explorer' => [
                    'workspace' => 'CampaignPlanningWorkspace',
                    'read_only' => true,
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                ],
                'recipient_explorer' => [
                    'workspace' => 'CampaignAudienceImportAttachmentOperatorWorkspace',
                    'read_only' => true,
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                ],
                'analytics' => [
                    'workspace' => 'CampaignAnalyticsWorkspace',
                    'read_only' => true,
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                ],
                'public_api' => [
                    'workspace' => 'CampaignPublicApiWorkspace',
                    'read_only' => true,
                    'host_registers_route' => true,
                    'package_registers_route' => false,
                ],
            ],
            hostResponsibilities: [
                'route_registration',
                'controller_composition',
                'authorization',
                'redaction',
                'request_validation',
                'operator_identity',
            ],
            packageResponsibilities: [
                'read_models',
                'workspace_contracts',
                'response_presenters',
                'effect_metadata',
            ],
            metadata: [
                ...$request->metadata,
                'source' => 'campaign-cockpit-consumption-map-builder',
                'read_only' => true,
                'registers_routes' => false,
                'registers_controllers' => false,
                'executes_mutations' => false,
            ],
            effects: $request->effects,
        );
    }
}
