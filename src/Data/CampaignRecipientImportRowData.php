<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignRecipientImportRowData extends Data
{
    /**
     * @param  array<string, mixed>  $raw
     * @param  array<string, string>  $errors
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $importId = null,
        public readonly ?string $audienceId = null,
        public readonly int $rowNumber = 1,
        public readonly string $status = 'valid',
        public readonly CampaignRecipientPlanningInputData $recipient = new CampaignRecipientPlanningInputData,
        public readonly array $raw = [],
        public readonly array $errors = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
