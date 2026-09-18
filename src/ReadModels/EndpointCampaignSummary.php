<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Models\EndpointCampaign;

final class EndpointCampaignSummary
{
    /** @return array<string, mixed> */
    public function forCampaign(EndpointCampaign $campaign): array
    {
        return [
            'reference' => $campaign->reference,
            'title' => $campaign->title,
            'description' => $campaign->description,
            'status' => $campaign->status,
            'merchant_display_name' => $campaign->merchant_display_name,
            'merchant_slug' => $campaign->merchant_slug,
            'endpoint_slug' => $campaign->endpoint_slug,
            'created_at' => $campaign->created_at?->toIso8601String(),
            'updated_at' => $campaign->updated_at?->toIso8601String(),
            'usage_count' => $campaign->usage_count,
            'starts_limit' => $campaign->starts_limit,
            'last_started_at' => $campaign->last_started_at?->toIso8601String(),
            'expires_at' => $campaign->expires_at?->toIso8601String(),
            'usage_key' => (string) data_get($campaign->settings, 'usage_key', data_get($campaign->settings, 'kind', 'lead')),
            'usage_label' => (string) data_get($campaign->settings, 'usage_label', 'Lead'),
            'capabilities' => array_values((array) data_get($campaign->settings, 'capabilities', [])),
            'availability' => (array) data_get($campaign->settings, 'availability', []),
            'limits' => (array) data_get($campaign->settings, 'limits', []),
        ];
    }
}
