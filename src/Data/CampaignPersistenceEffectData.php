<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignPersistenceEffectData extends Data
{
    public function __construct(
        public readonly bool $persists = false,
        public readonly bool $usesDatabase = false,
        public readonly bool $queuesJobs = false,
        public readonly bool $issuesPortableCodes = false,
        public readonly bool $sendsFeedback = false,
        public readonly bool $writesAuditLog = false,
        public readonly bool $movesMoney = false,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'persists' => $this->persists,
            'uses_database' => $this->usesDatabase,
            'queues_jobs' => $this->queuesJobs,
            'issues_pay_codes' => $this->issuesPortableCodes,
            'sends_feedback' => $this->sendsFeedback,
            'writes_journal' => $this->writesAuditLog,
            'moves_money' => $this->movesMoney,
        ];
    }
}
