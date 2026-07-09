<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiDescriptors;
use LBHurtado\XCampaign\Data\CampaignPublicApiRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiDescriptorBuilder;

it('builds public api descriptors without registering routes or controllers', function () {
    $descriptor = (new CampaignPublicApiDescriptorBuilder)->build(new CampaignPublicApiRequestData(
        planningKey: 'planning-api',
        executionId: 'execution-api',
        operatorId: 'operator-api',
        apiVersion: 'v1',
        metadata: ['request_id' => 'request-api'],
    ));

    expect($descriptor->status)->toBe('described')
        ->and($descriptor->endpoints)->toHaveKeys([
            'campaign.summary',
            'campaign.cockpit',
            'campaign.production_readiness',
            'campaign.host_integration',
        ])
        ->and($descriptor->endpoints['campaign.summary'])->toMatchArray([
            'method' => 'GET',
            'host_registers_route' => true,
            'package_registers_route' => false,
        ])
        ->and($descriptor->hostResponsibilities)->toContain('routes')
        ->and($descriptor->hostResponsibilities)->toContain('controllers')
        ->and($descriptor->hostResponsibilities)->toContain('request_validation')
        ->and($descriptor->packageResponsibilities)->toContain('descriptors')
        ->and($descriptor->metadata)->toMatchArray([
            'request_id' => 'request-api',
            'source' => 'campaign-public-api-descriptor-builder',
            'read_only' => true,
            'registers_routes' => false,
            'registers_controllers' => false,
            'owns_requests' => false,
            'owns_resources' => false,
        ]);
});

it('implements the public api descriptor builder contract', function () {
    expect(new CampaignPublicApiDescriptorBuilder)
        ->toBeInstanceOf(BuildsCampaignPublicApiDescriptors::class);
});
