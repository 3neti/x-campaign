<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('publishes the initial durable campaign planning tables through package migrations', function () {
    $this->artisan('migrate')->run();

    expect(Schema::hasTable('campaign_plans'))->toBeTrue()
        ->and(Schema::hasTable('campaign_audiences'))->toBeTrue()
        ->and(Schema::hasTable('campaign_recipients'))->toBeTrue()
        ->and(Schema::hasTable('campaign_imports'))->toBeTrue()
        ->and(Schema::hasTable('campaign_import_rows'))->toBeTrue();
});

it('creates portable identifier status and json context columns', function () {
    $this->artisan('migrate')->run();

    expect(Schema::hasColumns('campaign_plans', [
        'planning_key',
        'campaign_id',
        'status',
        'metadata',
        'effects',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('campaign_audiences', [
            'campaign_plan_id',
            'campaign_id',
            'audience_id',
            'status',
            'metadata',
            'effects',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('campaign_recipients', [
            'campaign_audience_id',
            'audience_id',
            'recipient_id',
            'status',
            'metadata',
            'effects',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('campaign_imports', [
            'campaign_audience_id',
            'audience_id',
            'import_id',
            'status',
            'source_payload',
            'review_payload',
            'metadata',
            'effects',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('campaign_import_rows', [
            'campaign_import_id',
            'import_id',
            'row_id',
            'status',
            'source_payload',
            'review_payload',
            'metadata',
            'effects',
        ]))->toBeTrue();
});
