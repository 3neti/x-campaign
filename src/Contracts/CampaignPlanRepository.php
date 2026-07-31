<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;

interface CampaignPlanRepository
{
    public function put(string $key, CampaignPlanData $plan): CampaignPlanData;

    public function get(string $key): ?CampaignPlanData;

    public function has(string $key): bool;

    /**
     * @return array<string, CampaignPlanData>
     */
    public function all(): array;

    public function forget(string $key): bool;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}
