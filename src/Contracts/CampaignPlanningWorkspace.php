<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignSummaryData;

interface CampaignPlanningWorkspace
{
    public function create(string $key, CampaignPlanningInputData $input): CampaignPlanData;

    public function get(string $key): ?CampaignPlanData;

    public function update(string $key, CampaignPlanningInputData $input): CampaignPlanData;

    public function schedule(string $key, string $scheduledAt): CampaignPlanData;

    public function archive(string $key, ?string $reason = null): CampaignPlanData;

    public function summary(string $key): ?CampaignSummaryData;

    /**
     * @return array<string, CampaignPlanData>
     */
    public function all(): array;

    /**
     * @return array<string, bool>
     */
    public function effects(): array;
}

