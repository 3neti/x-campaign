<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignPortableCodeGenerationResultData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $status,
        public readonly string $generationId,
        public readonly CampaignPortableCodeGenerationRequestData $request,
        public readonly ?string $portableCodeReference = null,
        public readonly array $blockers = [],
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }
}
