<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostIntegrationManifests;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationRequestData;
use LBHurtado\XCampaign\ReadModels\CampaignHostIntegrationManifestBuilder;

it('builds host-safe integration manifests without registering host infrastructure', function () {
    $manifest = (new CampaignHostIntegrationManifestBuilder)->build(new CampaignHostIntegrationRequestData(
        planningKey: 'planning-host',
        executionId: 'execution-host',
        operatorId: 'operator-host',
        channel: 'sms',
        correlationId: 'correlation-host',
        metadata: ['request_id' => 'request-host'],
    ));

    expect($manifest->status)->toBe('available')
        ->and($manifest->capabilities)->toMatchArray([
            'planning_workspace' => 'read_write_in_memory_boundary',
            'cockpit_summary' => 'read_only',
            'operational_monitor' => 'read_only',
            'production_readiness' => 'read_only',
        ])
        ->and($manifest->hostResponsibilities)->toContain('routes')
        ->and($manifest->hostResponsibilities)->toContain('controllers')
        ->and($manifest->hostResponsibilities)->toContain('authorization')
        ->and($manifest->hostResponsibilities)->toContain('redaction')
        ->and($manifest->packageResponsibilities)->toContain('read_models')
        ->and($manifest->packageResponsibilities)->toContain('workspaces')
        ->and($manifest->warnings)->toContain('host authorization required')
        ->and($manifest->metadata)->toMatchArray([
            'request_id' => 'request-host',
            'source' => 'campaign-host-integration-manifest-builder',
            'read_only' => true,
            'registers_routes' => false,
            'registers_controllers' => false,
            'owns_middleware' => false,
            'owns_policies' => false,
        ]);
});

it('implements the host integration manifest builder contract', function () {
    expect(new CampaignHostIntegrationManifestBuilder)
        ->toBeInstanceOf(BuildsCampaignHostIntegrationManifests::class);
});
