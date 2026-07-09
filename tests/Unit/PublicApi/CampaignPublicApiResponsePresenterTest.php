<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignPublicApiResponses;
use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiResponseData;
use LBHurtado\XCampaign\ReadModels\CampaignPublicApiResponsePresenter;

it('presents public api descriptors as host-safe response envelopes', function () {
    $response = (new CampaignPublicApiResponsePresenter)->present(new CampaignPublicApiDescriptorData(
        status: 'described',
        planningKey: 'planning-api',
        executionId: 'execution-api',
        operatorId: 'operator-api',
        apiVersion: 'v1',
        endpoints: ['campaign.summary' => ['method' => 'GET']],
        hostResponsibilities: ['routes', 'controllers', 'request_validation'],
        packageResponsibilities: ['descriptors', 'read_models'],
        metadata: ['request_id' => 'request-api'],
    ));

    expect($response)->toBeInstanceOf(CampaignPublicApiResponseData::class)
        ->and($response->status)->toBe('ok')
        ->and($response->data)->toMatchArray([
            'planning_key' => 'planning-api',
            'execution_id' => 'execution-api',
            'operator_id' => 'operator-api',
            'api_version' => 'v1',
            'descriptor_status' => 'described',
            'endpoints' => ['campaign.summary' => ['method' => 'GET']],
            'host_responsibilities' => ['routes', 'controllers', 'request_validation'],
            'package_responsibilities' => ['descriptors', 'read_models'],
        ])
        ->and($response->meta)->toMatchArray([
            'request_id' => 'request-api',
            'source' => 'campaign-public-api-response-presenter',
            'read_only' => true,
            'registers_routes' => false,
            'registers_controllers' => false,
            'owns_requests' => false,
            'owns_resources' => false,
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

it('implements the public api response presenter contract', function () {
    expect(new CampaignPublicApiResponsePresenter)
        ->toBeInstanceOf(PresentsCampaignPublicApiResponses::class);
});
