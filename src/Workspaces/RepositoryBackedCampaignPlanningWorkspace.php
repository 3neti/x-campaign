<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Workspaces;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\ArchivesCampaignPlans;
use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Contracts\CampaignPlanningWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;

class RepositoryBackedCampaignPlanningWorkspace implements CampaignPlanningWorkspace
{
    public function __construct(
        private readonly CampaignPlanRepository $repository,
        private readonly CreatesCampaignPlans $creator,
        private readonly UpdatesCampaignPlans $updater,
        private readonly SchedulesCampaignPlans $scheduler,
        private readonly ArchivesCampaignPlans $archiver,
        private readonly BuildsCampaignSummaries $summaries,
    ) {}

    public function create(string $key, CampaignPlanningInputData $input): CampaignPlanData
    {
        return $this->repository->put($key, $this->creator->handle($input));
    }

    public function get(string $key): ?CampaignPlanData
    {
        return $this->repository->get($key);
    }

    public function update(string $key, CampaignPlanningInputData $input): CampaignPlanData
    {
        return $this->repository->put(
            $key,
            $this->updater->handle($this->requirePlan($key), $input),
        );
    }

    public function schedule(string $key, string $scheduledAt): CampaignPlanData
    {
        return $this->repository->put(
            $key,
            $this->scheduler->handle($this->requirePlan($key), $scheduledAt),
        );
    }

    public function archive(string $key, ?string $reason = null): CampaignPlanData
    {
        return $this->repository->put(
            $key,
            $this->archiver->handle($this->requirePlan($key), $reason),
        );
    }

    public function summary(string $key): ?CampaignSummaryData
    {
        $plan = $this->repository->get($key);

        if (! $plan instanceof CampaignPlanData) {
            return null;
        }

        return $this->summaries->fromPlan($plan);
    }

    /**
     * @return array<string, CampaignPlanData>
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    /**
     * @return array<string, bool>
     */
    public function effects(): array
    {
        return [
            ...$this->repository->effects(),
            'integrates_repository' => true,
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }

    private function requirePlan(string $key): CampaignPlanData
    {
        $plan = $this->repository->get($key);

        if (! $plan instanceof CampaignPlanData) {
            throw new InvalidArgumentException("Unknown campaign planning key [{$key}].");
        }

        return $plan;
    }
}
