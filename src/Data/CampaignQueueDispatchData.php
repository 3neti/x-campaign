<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignQueueDispatchData extends Data
{
    public readonly CampaignPersistenceEffectData $effects;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $job,
        public readonly array $payload = [],
        public readonly string $queue = 'campaigns',
        public readonly ?string $connection = null,
        public readonly int $delaySeconds = 0,
        public readonly array $metadata = [],
        ?CampaignPersistenceEffectData $effects = null,
    ) {
        $this->effects = $effects ?? new CampaignPersistenceEffectData;
    }

    /**
     * @return array{
     *     planning_key: string,
     *     queue: string,
     *     connection: string|null,
     *     job: string,
     *     payload: array<string, mixed>,
     *     delay_seconds: int,
     *     metadata: array<string, mixed>,
     *     effects: array<string, bool>
     * }
     */
    public function toArray(): array
    {
        return [
            'planning_key' => $this->planningKey,
            'queue' => $this->queue,
            'connection' => $this->connection,
            'job' => $this->job,
            'payload' => $this->payload,
            'delay_seconds' => $this->delaySeconds,
            'metadata' => $this->metadata,
            'effects' => $this->effects->toArray(),
        ];
    }
}
