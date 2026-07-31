<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportPlanningInputData extends Data
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $audienceId = null,
        public readonly string $source = 'manual',
        public readonly ?string $sourceReference = null,
        public readonly int $expectedRecipientCount = 0,
        public readonly array $columns = [],
        public readonly array $metadata = [],
    ) {}
}
