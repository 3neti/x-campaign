<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use LBHurtado\XCampaign\Models\EndpointCampaign;

final class EndpointCampaignAvailability
{
    public function ensureStartable(EndpointCampaign $campaign): void
    {
        if ($campaign->status !== 'active') {
            throw ValidationException::withMessages([
                'campaign' => 'This Lead Campaign is not accepting new prospects.',
            ]);
        }

        if ($campaign->expires_at !== null && $campaign->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'campaign' => 'This Lead Campaign has expired.',
            ]);
        }

        $this->ensureAvailabilityWindow($campaign);

        if (
            $campaign->starts_limit !== null
            && $campaign->usage_count >= $campaign->starts_limit
        ) {
            throw ValidationException::withMessages([
                'campaign' => 'This Lead Campaign has reached its prospect limit.',
            ]);
        }

    }

    private function ensureAvailabilityWindow(EndpointCampaign $campaign): void
    {
        $availability = (array) data_get((array) $campaign->settings, 'availability', []);
        $timezone = (string) ($availability['timezone'] ?? config('app.timezone', 'UTC'));

        if (filled($availability['starts_at'] ?? null)) {
            $startsAt = Carbon::parse((string) $availability['starts_at'], $timezone);

            if ($startsAt->isFuture()) {
                throw ValidationException::withMessages([
                    'campaign' => 'This Lead Campaign is not open yet.',
                ]);
            }
        }

        $dailyStart = $availability['daily_window_start'] ?? null;
        $dailyEnd = $availability['daily_window_end'] ?? null;

        if (! is_string($dailyStart) || ! is_string($dailyEnd)) {
            return;
        }

        $now = Carbon::now($timezone);
        $current = $now->format('H:i');
        $isOpen = $dailyStart <= $dailyEnd
            ? $current >= $dailyStart && $current <= $dailyEnd
            : $current >= $dailyStart || $current <= $dailyEnd;

        if (! $isOpen) {
            throw ValidationException::withMessages([
                'campaign' => 'This Lead Campaign is outside its daily operating window.',
            ]);
        }
    }
}
