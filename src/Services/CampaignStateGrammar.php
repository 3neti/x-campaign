<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use BackedEnum;
use LBHurtado\XCampaign\Enums\CampaignAudienceStatus;
use LBHurtado\XCampaign\Enums\CampaignExecutionStatus;
use LBHurtado\XCampaign\Enums\CampaignStatus;

class CampaignStateGrammar
{
    public function canTransition(BackedEnum $from, BackedEnum $to): bool
    {
        return in_array($to->value, $this->transitionsFor($from), true);
    }

    /**
     * @return array<int, string>
     */
    public function transitionsFor(BackedEnum $status): array
    {
        return match ($status) {
            CampaignStatus::Draft => [
                CampaignStatus::Scheduled->value,
                CampaignStatus::Running->value,
                CampaignStatus::Cancelled->value,
                CampaignStatus::Archived->value,
            ],
            CampaignStatus::Scheduled => [
                CampaignStatus::Running->value,
                CampaignStatus::Cancelled->value,
                CampaignStatus::Archived->value,
            ],
            CampaignStatus::Running => [
                CampaignStatus::Paused->value,
                CampaignStatus::Completed->value,
                CampaignStatus::Cancelled->value,
            ],
            CampaignStatus::Paused => [
                CampaignStatus::Running->value,
                CampaignStatus::Cancelled->value,
            ],
            CampaignStatus::Completed, CampaignStatus::Cancelled => [
                CampaignStatus::Archived->value,
            ],
            CampaignStatus::Archived => [],
            CampaignAudienceStatus::Draft => [
                CampaignAudienceStatus::Importing->value,
                CampaignAudienceStatus::Ready->value,
                CampaignAudienceStatus::Archived->value,
            ],
            CampaignAudienceStatus::Importing => [
                CampaignAudienceStatus::Ready->value,
                CampaignAudienceStatus::Archived->value,
            ],
            CampaignAudienceStatus::Ready => [
                CampaignAudienceStatus::Locked->value,
                CampaignAudienceStatus::Archived->value,
            ],
            CampaignAudienceStatus::Locked => [
                CampaignAudienceStatus::Ready->value,
                CampaignAudienceStatus::Archived->value,
            ],
            CampaignAudienceStatus::Archived => [],
            CampaignExecutionStatus::Planned => [
                CampaignExecutionStatus::Queued->value,
                CampaignExecutionStatus::Running->value,
                CampaignExecutionStatus::Cancelled->value,
            ],
            CampaignExecutionStatus::Queued => [
                CampaignExecutionStatus::Running->value,
                CampaignExecutionStatus::Cancelled->value,
            ],
            CampaignExecutionStatus::Running => [
                CampaignExecutionStatus::Paused->value,
                CampaignExecutionStatus::Completed->value,
                CampaignExecutionStatus::Failed->value,
                CampaignExecutionStatus::Cancelled->value,
            ],
            CampaignExecutionStatus::Paused => [
                CampaignExecutionStatus::Running->value,
                CampaignExecutionStatus::Cancelled->value,
            ],
            CampaignExecutionStatus::Failed => [
                CampaignExecutionStatus::Queued->value,
                CampaignExecutionStatus::Cancelled->value,
            ],
            CampaignExecutionStatus::Completed, CampaignExecutionStatus::Cancelled => [],
            default => [],
        };
    }
}
