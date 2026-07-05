<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudiencePlanData extends Data
{
    /**
     * @param  array<int, CampaignRecipientData>  $recipients
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignAudienceData $audience,
        public readonly array $recipients = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
