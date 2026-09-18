<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use LBHurtado\XCampaign\Contracts\EndpointCampaignRepository;
use LBHurtado\XCampaign\Models\EndpointCampaign;

final class EloquentEndpointCampaignRepository implements EndpointCampaignRepository
{
    public function __construct(private readonly EndpointCampaign $model = new EndpointCampaign) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): EndpointCampaign
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function findByPublicEndpointOrFail(string $merchantSlug, string $endpointSlug): EndpointCampaign
    {
        return $this->model->newQuery()
            ->where('merchant_slug', $merchantSlug)
            ->where('endpoint_slug', $endpointSlug)
            ->firstOrFail();
    }

    public function publicEndpointExists(string $merchantSlug, string $endpointSlug): bool
    {
        return $this->model->newQuery()
            ->where('merchant_slug', $merchantSlug)
            ->where('endpoint_slug', $endpointSlug)
            ->exists();
    }

    public function merchantSlugUsedByOtherOwner(string $merchantSlug, string $ownerType, string $ownerId): bool
    {
        return $this->model->newQuery()
            ->where('merchant_slug', $merchantSlug)
            ->where(function ($query) use ($ownerType, $ownerId): void {
                $query->where('owner_type', '!=', $ownerType)
                    ->orWhere('owner_id', '!=', $ownerId);
            })
            ->exists();
    }

    public function recordSuccessfulStart(EndpointCampaign $campaign): void
    {
        $campaign->newQuery()
            ->whereKey($campaign->getKey())
            ->update([
                'usage_count' => DB::raw('usage_count + 1'),
                'last_started_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /** @return Collection<int, EndpointCampaign> */
    public function recentForOwner(string $ownerType, string $ownerId): Collection
    {
        return $this->model->newQuery()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->latest('updated_at')
            ->limit(25)
            ->get();
    }
}
