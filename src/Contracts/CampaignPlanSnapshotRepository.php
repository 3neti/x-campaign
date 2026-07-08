<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanSnapshotData;

interface CampaignPlanSnapshotRepository
{
    public function put(string $key, CampaignPlanSnapshotData $snapshot): CampaignPlanSnapshotData;

    public function get(string $key): ?CampaignPlanSnapshotData;

    public function has(string $key): bool;

    /**
     * @return array<string, CampaignPlanSnapshotData>
     */
    public function all(): array;

    public function forget(string $key): bool;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
