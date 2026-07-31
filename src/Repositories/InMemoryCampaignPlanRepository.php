<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Repositories;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Data\CampaignPlanData;

class InMemoryCampaignPlanRepository implements CampaignPlanRepository
{
    /**
     * @var array<string, CampaignPlanData>
     */
    private array $plans = [];

    public function put(string $key, CampaignPlanData $plan): CampaignPlanData
    {
        $this->plans[$this->normalizeKey($key)] = $plan;

        return $plan;
    }

    public function get(string $key): ?CampaignPlanData
    {
        return $this->plans[$this->normalizeKey($key)] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($this->normalizeKey($key), $this->plans);
    }

    /**
     * @return array<string, CampaignPlanData>
     */
    public function all(): array
    {
        return $this->plans;
    }

    public function forget(string $key): bool
    {
        $key = $this->normalizeKey($key);

        if (! array_key_exists($key, $this->plans)) {
            return false;
        }

        unset($this->plans[$key]);

        return true;
    }

    /**
     * @return array<string, bool>
     */
    public function effects(): array
    {
        return [
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
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
