<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanSnapshotData;
use LBHurtado\XCampaign\Models\CampaignPlan;

class EloquentCampaignPlanSnapshotRepository implements CampaignPlanSnapshotRepository
{
    public function put(string $key, CampaignPlanSnapshotData $snapshot): CampaignPlanSnapshotData
    {
        $key = $this->normalizeKey($key);
        $plan = $snapshot->plan;

        CampaignPlan::query()->updateOrCreate(
            ['planning_key' => $key],
            [
                'campaign_id' => $plan->campaign->id ?? $key,
                'name' => $plan->campaign->name,
                'status' => $plan->campaign->status,
                'metadata' => [
                    ...$snapshot->metadata,
                    'snapshot' => $snapshot->toArray(),
                ],
                'effects' => $this->effects(),
            ],
        );

        return $snapshot;
    }

    public function get(string $key): ?CampaignPlanSnapshotData
    {
        $record = CampaignPlan::query()
            ->where('planning_key', $this->normalizeKey($key))
            ->first();

        if (! $record instanceof CampaignPlan) {
            return null;
        }

        $snapshot = $record->metadata['snapshot'] ?? null;

        if (is_array($snapshot)) {
            return CampaignPlanSnapshotData::from($snapshot);
        }

        return new CampaignPlanSnapshotData(
            planningKey: (string) $record->planning_key,
            plan: CampaignPlanData::from([
                'campaign' => [
                    'id' => $record->campaign_id,
                    'name' => $record->name ?? '',
                    'status' => $record->status,
                ],
            ]),
            metadata: is_array($record->metadata) ? $record->metadata : [],
        );
    }

    public function has(string $key): bool
    {
        return CampaignPlan::query()
            ->where('planning_key', $this->normalizeKey($key))
            ->exists();
    }

    /**
     * @return array<string, CampaignPlanSnapshotData>
     */
    public function all(): array
    {
        return CampaignPlan::query()
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (CampaignPlan $record): array {
                $snapshot = $this->get((string) $record->planning_key);

                return $snapshot instanceof CampaignPlanSnapshotData
                    ? [(string) $record->planning_key => $snapshot]
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
            throw new InvalidArgumentException('Campaign planning snapshot repository key must not be empty.');
        }

        return $key;
    }
}
