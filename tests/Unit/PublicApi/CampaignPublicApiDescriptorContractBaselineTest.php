<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\BuildsCampaignPublicApiDescriptors;
use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Data\CampaignPublicApiRequestData;

it('defines a public api request without owning host api infrastructure', function () {
    $request = new CampaignPublicApiRequestData(
        planningKey: 'planning-api',
        executionId: 'execution-api',
        operatorId: 'operator-api',
        apiVersion: 'v1',
        metadata: ['request_id' => 'request-api'],
    );

    expect($request->planningKey)->toBe('planning-api')
        ->and($request->apiVersion)->toBe('v1')
        ->and($request->metadata)->toMatchArray(['request_id' => 'request-api'])
        ->and($request->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a public api descriptor as endpoint description only', function () {
    $descriptor = new CampaignPublicApiDescriptorData(
        status: 'described',
        planningKey: 'planning-api',
        executionId: 'execution-api',
        operatorId: 'operator-api',
        apiVersion: 'v1',
        endpoints: ['campaign.summary' => ['method' => 'GET']],
        hostResponsibilities: ['routes', 'controllers', 'validation'],
        packageResponsibilities: ['descriptors', 'response_presenters'],
        metadata: ['source' => 'test'],
    );

    expect($descriptor->status)->toBe('described')
        ->and($descriptor->endpoints)->toMatchArray(['campaign.summary' => ['method' => 'GET']])
        ->and($descriptor->hostResponsibilities)->toContain('routes')
        ->and($descriptor->packageResponsibilities)->toContain('descriptors')
        ->and($descriptor->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('defines a public api descriptor builder contract', function () {
    expect(interface_exists(BuildsCampaignPublicApiDescriptors::class))->toBeTrue()
        ->and(method_exists(BuildsCampaignPublicApiDescriptors::class, 'build'))->toBeTrue();
});
