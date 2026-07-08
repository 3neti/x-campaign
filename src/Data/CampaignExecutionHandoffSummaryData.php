<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignExecutionHandoffSummaryData extends Data
{
    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $status,
        public readonly string $handoffId,
        public readonly string $planningKey,
        public readonly string $executionId,
        public readonly int $batchCount,
        public readonly int $recipientCount,
        public readonly bool $ready,
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
