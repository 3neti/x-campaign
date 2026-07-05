<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignImportData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $audienceId = null,
        public readonly string $source = 'manual',
        public readonly string $status = 'pending',
        public readonly int $recipientCount = 0,
        public readonly array $metadata = [],
    ) {}
}
