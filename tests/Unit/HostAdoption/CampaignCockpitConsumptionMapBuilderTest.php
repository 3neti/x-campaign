<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitConsumptionMaps;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitConsumptionMapBuilder;

it('builds a read-only cockpit consumption map for x-change', function () {
    $map = (new CampaignCockpitConsumptionMapBuilder)->build(new CampaignXChangeIntegrationRequestData(
        planningKey: 'planning-cockpit',
        executionId: 'execution-cockpit',
        operatorId: 'operator-cockpit',
        metadata: ['request_id' => 'request-cockpit'],
    ));

    expect($map->status)->toBe('described')
        ->and($map->surfaces)->toHaveKeys([
            'dashboard',
            'campaign_explorer',
            'recipient_explorer',
            'analytics',
            'public_api',
        ])
        ->and($map->surfaces['dashboard'])->toMatchArray([
            'workspace' => 'CampaignCockpitWorkspace',
            'read_only' => true,
            'host_registers_route' => true,
            'package_registers_route' => false,
        ])
        ->and($map->hostResponsibilities)->toContain('route_registration')
        ->and($map->hostResponsibilities)->toContain('authorization')
        ->and($map->hostResponsibilities)->toContain('redaction')
        ->and($map->packageResponsibilities)->toContain('read_models')
        ->and($map->packageResponsibilities)->toContain('workspace_contracts')
        ->and($map->metadata)->toMatchArray([
            'request_id' => 'request-cockpit',
            'source' => 'campaign-cockpit-consumption-map-builder',
            'read_only' => true,
            'registers_routes' => false,
            'registers_controllers' => false,
            'executes_mutations' => false,
        ]);
});

it('implements the cockpit consumption map builder contract', function () {
    expect(new CampaignCockpitConsumptionMapBuilder)
        ->toBeInstanceOf(BuildsCampaignCockpitConsumptionMaps::class);
});
