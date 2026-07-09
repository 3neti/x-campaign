<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignHostIntegrationManifests;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationRequestData;

it('defines a host integration request without owning host infrastructure', function () {
    $request = new CampaignHostIntegrationRequestData(
        planningKey: 'planning-host',
        executionId: 'execution-host',
        operatorId: 'operator-host',
        channel: 'sms',
        correlationId: 'correlation-host',
        metadata: ['request_id' => 'request-host'],
    );

    expect($request->planningKey)->toBe('planning-host')
        ->and($request->channel)->toBe('sms')
        ->and($request->correlationId)->toBe('correlation-host')
        ->and($request->metadata)->toMatchArray(['request_id' => 'request-host'])
        ->and($request->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a host integration manifest as package-side capability description only', function () {
    $manifest = new CampaignHostIntegrationManifestData(
        status: 'available',
        planningKey: 'planning-host',
        executionId: 'execution-host',
        operatorId: 'operator-host',
        capabilities: ['cockpit_summary' => 'read_only'],
        hostResponsibilities: ['routes', 'controllers', 'authorization', 'redaction'],
        packageResponsibilities: ['read_models', 'workspaces', 'presenters'],
        warnings: ['host authorization required'],
        metadata: ['source' => 'test'],
    );

    expect($manifest->status)->toBe('available')
        ->and($manifest->capabilities)->toMatchArray(['cockpit_summary' => 'read_only'])
        ->and($manifest->hostResponsibilities)->toBe(['routes', 'controllers', 'authorization', 'redaction'])
        ->and($manifest->packageResponsibilities)->toBe(['read_models', 'workspaces', 'presenters'])
        ->and($manifest->warnings)->toBe(['host authorization required'])
        ->and($manifest->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a host integration manifest builder contract', function () {
    expect(interface_exists(BuildsCampaignHostIntegrationManifests::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignHostIntegrationManifests::class, 'build'))->toBeTrue();
});
