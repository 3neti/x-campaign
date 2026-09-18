<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use Illuminate\Database\Eloquent\Collection;
use LBHurtado\XCampaign\Models\EndpointCampaign;

interface EndpointCampaignRepository
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): EndpointCampaign;

    public function findByPublicEndpointOrFail(string $merchantSlug, string $endpointSlug): EndpointCampaign;

    public function publicEndpointExists(string $merchantSlug, string $endpointSlug): bool;

    public function merchantSlugUsedByOtherOwner(string $merchantSlug, string $ownerType, string $ownerId): bool;

    public function recordSuccessfulStart(EndpointCampaign $campaign): void;

    /** @return Collection<int, EndpointCampaign> */
    public function recentForOwner(string $ownerType, string $ownerId): Collection;
}
