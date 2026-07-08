<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Models\CampaignPlan;

class EloquentCampaignPlanRepository implements CampaignPlanRepository
{
    public function put(string $key, CampaignPlanData $plan): CampaignPlanData
    {
        $key = $this->normalizeKey($key);

        CampaignPlan::query()->updateOrCreate(
            ['planning_key' => $key],
            [
                'campaign_id' => $plan->campaign->id ?? $key,
                'name' => $plan->campaign->name,
                'status' => $plan->campaign->status,
                'metadata' => [
                    ...$plan->metadata,
                    'plan' => $plan->toArray(),
                ],
                'effects' => $this->effects(),
            ],
        );

        return $plan;
    }

    public function get(string $key): ?CampaignPlanData
    {
        $record = CampaignPlan::query()
            ->where('planning_key', $this->normalizeKey($key))
            ->first();

        if (! $record instanceof CampaignPlan) {
            return null;
        }

        $plan = $record->metadata['plan'] ?? null;

        if (is_array($plan)) {
            return CampaignPlanData::from($plan);
        }

        return CampaignPlanData::from([
            'campaign' => [
                'id' => $record->campaign_id,
                'name' => $record->name ?? '',
                'status' => $record->status,
            ],
        ]);
    }

    public function has(string $key): bool
    {
        return CampaignPlan::query()
            ->where('planning_key', $this->normalizeKey($key))
            ->exists();
    }

    /**
     * @return array<string, CampaignPlanData>
     */
    public function all(): array
    {
        return CampaignPlan::query()
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (CampaignPlan $record): array {
                $plan = $this->get((string) $record->planning_key);

                return $plan instanceof CampaignPlanData
                    ? [(string) $record->planning_key => $plan]
                    : [];
            })
            ->all();
    }

    public function forget(string $key): bool
    {
        return CampaignPlan::query()
            ->where('planning_key', $this->normalizeKey($key))
            ->delete() > 0;
    }

    /**
     * @return array<string, bool>
     */
    public function effects(): array
    {
        return (new CampaignPersistenceEffectData(
            persists: true,
            usesDatabase: true,
        ))->toArray();
    }

    private function normalizeKey(string $key): string
    {
        $key = trim($key);

        if ($key === '') {
            throw new InvalidArgumentException('Campaign planning repository key must not be empty.');
        }

        return $key;
    }
}
