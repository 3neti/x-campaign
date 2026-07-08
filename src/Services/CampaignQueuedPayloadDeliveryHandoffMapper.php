<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToDeliveryHandoffs;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

class CampaignQueuedPayloadDeliveryHandoffMapper implements MapsCampaignQueuedPayloadsToDeliveryHandoffs
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignDeliveryHandoffWorkspaceInputData
    {
        if ($payload->operation !== 'delivery.handoff') {
            throw new InvalidArgumentException("Queued campaign payload operation [{$payload->operation}] cannot be mapped to a delivery handoff.");
        }

        $executionId = $payload->payload['execution_id'] ?? null;

        if (! is_scalar($executionId) || trim((string) $executionId) === '') {
            throw new InvalidArgumentException('Queued delivery handoff payloads require an execution_id.');
        }

        $channel = $payload->payload['channel'] ?? 'sms';
        $requestedBy = $payload->metadata['operator_id'] ?? $payload->metadata['requested_by'] ?? 'queue';

        return new CampaignDeliveryHandoffWorkspaceInputData(
            planningKey: $payload->planningKey,
            executionId: (string) $executionId,
            channel: is_scalar($channel) && trim((string) $channel) !== '' ? (string) $channel : 'sms',
            requestedBy: is_scalar($requestedBy) && trim((string) $requestedBy) !== '' ? (string) $requestedBy : 'queue',
            correlationId: $payload->correlationId,
            metadata: [
                ...$payload->metadata,
                'operation' => $payload->operation,
                'source' => 'queued-payload-delivery-handoff-mapper',
                'handoff_only' => true,
            ],
        );
    }
}

