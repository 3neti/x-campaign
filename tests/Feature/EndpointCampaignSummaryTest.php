<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use LBHurtado\XCampaign\Models\EndpointCampaign;
use LBHurtado\XCampaign\ReadModels\EndpointCampaignSummary;

it('projects only campaign fields with the existing defaults and no database access', function (): void {
    $campaign = new EndpointCampaign([
        'title' => 'Application', 'status' => 'active', 'usage_count' => '7',
        'merchant_display_name' => 'Merchant', 'merchant_slug' => 'merchant', 'endpoint_slug' => 'apply',
    ]);
    $campaign->reference = 'campaign-reference';
    DB::enableQueryLog();
    DB::flushQueryLog();

    $summary = app(EndpointCampaignSummary::class)->forCampaign($campaign);

    expect($summary)->toBe([
        'reference' => 'campaign-reference', 'title' => 'Application', 'description' => null,
        'status' => 'active', 'merchant_display_name' => 'Merchant', 'merchant_slug' => 'merchant',
        'endpoint_slug' => 'apply', 'created_at' => null, 'updated_at' => null,
        'usage_count' => 7, 'starts_limit' => null,
        'last_started_at' => null, 'expires_at' => null, 'usage_key' => 'lead', 'usage_label' => 'Lead',
        'capabilities' => [], 'availability' => [], 'limits' => [],
    ])->and(DB::getQueryLog())->toBeEmpty();
    DB::disableQueryLog();
});

it('preserves custom usage, indexed capabilities, limits and timestamp formats', function (): void {
    $campaign = new EndpointCampaign([
        'starts_limit' => 25, 'last_started_at' => '2026-09-18T04:00:00+00:00',
        'expires_at' => '2026-09-19T04:00:00+00:00',
        'settings' => [
            'kind' => 'legacy-kind', 'usage_key' => 'custom', 'usage_label' => 'Insurance',
            'capabilities' => [3 => 'public_endpoint', 7 => 'collection'],
            'availability' => ['timezone' => 'Asia/Manila'], 'limits' => ['budget_cap_minor' => 500],
        ],
    ]);
    $campaign->created_at = '2026-09-17T04:00:00+00:00';
    $campaign->updated_at = '2026-09-18T05:00:00+00:00';
    $builder = app(EndpointCampaignSummary::class);
    expect($builder->forCampaign($campaign))->toMatchArray([
        'created_at' => '2026-09-17T04:00:00+00:00',
        'updated_at' => '2026-09-18T05:00:00+00:00',
        'starts_limit' => 25, 'last_started_at' => '2026-09-18T04:00:00+00:00',
        'expires_at' => '2026-09-19T04:00:00+00:00', 'usage_key' => 'custom', 'usage_label' => 'Insurance',
        'capabilities' => ['public_endpoint', 'collection'],
        'availability' => ['timezone' => 'Asia/Manila'], 'limits' => ['budget_cap_minor' => 500],
    ]);
    $campaign->settings = ['kind' => 'legacy-kind'];
    expect($builder->forCampaign($campaign)['usage_key'])->toBe('legacy-kind');
    $campaign->settings = ['usage_key' => null, 'usage_label' => null];
    expect($builder->forCampaign($campaign)['usage_key'])->toBe('')
        ->and($builder->forCampaign($campaign)['usage_label'])->toBe('');
});
