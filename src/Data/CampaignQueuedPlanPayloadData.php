<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use InvalidArgumentException;
use Spatie\LaravelData\Data;

class CampaignQueuedPlanPayloadData extends Data
{
    public readonly string $correlationId;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey,
        public readonly string $operation,
        public readonly array $payload = [],
        public readonly array $metadata = [],
        ?string $correlationId = null,
    ) {
        $this->guardCompletePayload();

        $this->correlationId = $correlationId !== null && trim($correlationId) !== ''
            ? $correlationId
            : $this->defaultCorrelationId();
    }

    /**
     * @return array{
     *     planning_key: string,
     *     operation: string,
     *     payload: array<string, mixed>,
     *     metadata: array<string, mixed>,
     *     correlation_id: string
     * }
     */
    public function toArray(): array
    {
        return [
            'planning_key' => $this->planningKey,
            'operation' => $this->operation,
            'payload' => $this->payload,
            'metadata' => $this->metadata,
            'correlation_id' => $this->correlationId,
        ];
    }

    private function guardCompletePayload(): void
    {
        if (trim($this->planningKey) === '') {
            throw new InvalidArgumentException('Queued campaign payloads require a planning key.');
        }

        if (trim($this->operation) === '') {
            throw new InvalidArgumentException('Queued campaign payloads require an operation.');
        }
    }

    private function defaultCorrelationId(): string
    {
        $fingerprint = json_encode([
            'planning_key' => $this->planningKey,
            'operation' => $this->operation,
            'payload' => $this->payload,
        ], JSON_THROW_ON_ERROR);

        return 'campaign-queue-'.substr(hash('sha256', $fingerprint), 0, 24);
    }
}
