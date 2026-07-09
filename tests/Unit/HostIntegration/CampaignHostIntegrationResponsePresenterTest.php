<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignHostIntegrationResponses;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationManifestData;
use LBHurtado\XCampaign\Data\CampaignHostIntegrationResponseData;
use LBHurtado\XCampaign\ReadModels\CampaignHostIntegrationResponsePresenter;

it('presents host integration manifests as host-safe response envelopes', function () {
    $response = (new CampaignHostIntegrationResponsePresenter)->present(new CampaignHostIntegrationManifestData(
        status: 'available',
        planningKey: 'planning-host',
        executionId: 'execution-host',
        operatorId: 'operator-host',
        capabilities: ['cockpit_summary' => 'read_only'],
        hostResponsibilities: ['routes', 'controllers', 'authorization'],
        packageResponsibilities: ['read_models', 'workspaces'],
        warnings: ['host authorization required'],
        metadata: ['request_id' => 'request-host'],
    ));

    expect($response)->toBeInstanceOf(CampaignHostIntegrationResponseData::class)
        ->and($response->status)->toBe('ok')
        ->and($response->data)->toMatchArray([
            'planning_key' => 'planning-host',
            'execution_id' => 'execution-host',
            'operator_id' => 'operator-host',
            'manifest_status' => 'available',
            'capabilities' => ['cockpit_summary' => 'read_only'],
            'host_responsibilities' => ['routes', 'controllers', 'authorization'],
            'package_responsibilities' => ['read_models', 'workspaces'],
            'warnings' => ['host authorization required'],
        ])
        ->and($response->meta)->toMatchArray([
            'request_id' => 'request-host',
            'source' => 'campaign-host-integration-response-presenter',
            'read_only' => true,
            'registers_routes' => false,
            'registers_controllers' => false,
            'owns_middleware' => false,
            'owns_policies' => false,
        ])
        ->and($response->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('implements the host integration response presenter contract', function () {
    expect(new CampaignHostIntegrationResponsePresenter)
        ->toBeInstanceOf(PresentsCampaignHostIntegrationResponses::class);
});
