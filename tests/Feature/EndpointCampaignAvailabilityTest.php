<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use LBHurtado\XCampaign\Models\EndpointCampaign;
use LBHurtado\XCampaign\Services\EndpointCampaignAvailability;

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-09-18 04:00:00', 'UTC'));
    config(['app.timezone' => 'UTC']);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('preserves endpoint availability and validation precedence', function (array $attributes, ?string $message): void {
    $campaign = new EndpointCampaign([
        'status' => 'active', 'usage_count' => 0, 'settings' => [], ...$attributes,
    ]);
    $before = $campaign->getAttributes();
    $errors = null;

    try {
        app(EndpointCampaignAvailability::class)->ensureStartable($campaign);
    } catch (ValidationException $exception) {
        $errors = $exception->errors();
    }

    expect($errors)->toBe($message === null ? null : ['campaign' => [$message]])
        ->and($campaign->getAttributes())->toBe($before);
})->with([
    'open' => [[], null],
    'paused before expiry' => [['status' => 'paused', 'expires_at' => '2026-09-17'], 'This Lead Campaign is not accepting new prospects.'],
    'unknown fails closed' => [['status' => 'unknown'], 'This Lead Campaign is not accepting new prospects.'],
    'expired before limits' => [['expires_at' => '2026-09-17', 'starts_limit' => 0], 'This Lead Campaign has expired.'],
    'expiry exact instant remains open' => [['expires_at' => '2026-09-18 04:00:00'], null],
    'limit reached' => [['usage_count' => 2, 'starts_limit' => 2], 'This Lead Campaign has reached its prospect limit.'],
    'zero limit' => [['starts_limit' => 0], 'This Lead Campaign has reached its prospect limit.'],
    'below limit' => [['usage_count' => 1, 'starts_limit' => 2], null],
    'future start' => [['settings' => ['availability' => ['starts_at' => '2026-09-19']]], 'This Lead Campaign is not open yet.'],
    'start exact instant' => [['settings' => ['availability' => ['starts_at' => '2026-09-18T04:00:00Z']]], null],
    'timezone inclusive start' => [['settings' => ['availability' => ['timezone' => 'Asia/Manila', 'daily_window_start' => '12:00', 'daily_window_end' => '13:00']]], null],
    'timezone inclusive end' => [['settings' => ['availability' => ['timezone' => 'Asia/Manila', 'daily_window_start' => '11:00', 'daily_window_end' => '12:00']]], null],
    'outside daily hours' => [['settings' => ['availability' => ['daily_window_start' => '09:00', 'daily_window_end' => '17:00']]], 'This Lead Campaign is outside its daily operating window.'],
    'overnight window' => [['settings' => ['availability' => ['daily_window_start' => '22:00', 'daily_window_end' => '06:00']]], null],
    'overnight closed' => [['settings' => ['availability' => ['daily_window_start' => '22:00', 'daily_window_end' => '03:00']]], 'This Lead Campaign is outside its daily operating window.'],
    'partial daily window remains unrestricted' => [['settings' => ['availability' => ['daily_window_start' => '09:00']]], null],
]);

it('preserves the deployed table and endpoint casts without a template dependency', function (): void {
    $campaign = new EndpointCampaign([
        'usage_count' => '3', 'starts_limit' => '5', 'settings' => ['usage_key' => 'custom'],
        'expires_at' => '2026-09-19 00:00:00',
    ]);

    expect($campaign->getTable())->toBe('x_change_lead_campaigns')
        ->and($campaign->usage_count)->toBe(3)
        ->and($campaign->starts_limit)->toBe(5)
        ->and($campaign->settings)->toBe(['usage_key' => 'custom'])
        ->and($campaign->expires_at)->toBeInstanceOf(Carbon::class)
        ->and(method_exists($campaign, 'payCodeTemplate'))->toBeFalse();
});
