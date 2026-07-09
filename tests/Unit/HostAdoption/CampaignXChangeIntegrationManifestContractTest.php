<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignXChangeIntegrationManifests;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignXChangeIntegrationRequestData;

it('defines x-change integration manifest DTOs with safe defaults', function () {
    $request = new CampaignXChangeIntegrationRequestData(
        planningKey: 'planning-host',
        executionId: 'execution-host',
        operatorId: 'operator-host',
        metadata: ['request_id' => 'request-host'],
    );

    expect($request->hostApp)->toBe('x-change')
        ->and($request->metadata)->toBe(['request_id' => 'request-host'])
        ->and($request->effects)->toBeInstanceOf(CampaignPersistenceEffectData::class)
        ->and($request->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);

    $manifest = new CampaignXChangeIntegrationManifestData(
        status: 'described',
        planningKey: 'planning-host',
        executionId: 'execution-host',
        operatorId: 'operator-host',
        hostApp: 'x-change',
        capabilities: ['campaign_public_api' => 'read_only'],
        hostResponsibilities: ['routes', 'controllers', 'authorization'],
        packageResponsibilities: ['descriptors', 'workspaces'],
        unsupportedOperations: ['package_owned_routes'],
        metadata: ['request_id' => 'request-host'],
    );

    expect($manifest->status)->toBe('described')
        ->and($manifest->capabilities)->toBe(['campaign_public_api' => 'read_only'])
        ->and($manifest->hostResponsibilities)->toContain('routes')
        ->and($manifest->packageResponsibilities)->toContain('descriptors')
        ->and($manifest->unsupportedOperations)->toContain('package_owned_routes')
        ->and($manifest->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines the x-change integration manifest builder contract', function () {
    expect(interface_exists(BuildsCampaignXChangeIntegrationManifests::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignXChangeIntegrationManifests::class, 'build'))->toBeTrue();

    $method = new ReflectionMethod(BuildsCampaignXChangeIntegrationManifests::class, 'build');

    expect($method->getParameters()[0]->getType()?->getName())->toBe(CampaignXChangeIntegrationRequestData::class)
        ->and($method->getReturnType()?->getName())->toBe(CampaignXChangeIntegrationManifestData::class);
});
