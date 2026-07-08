<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExecutionHandoffs;
use LBHurtado\XCampaign\Data\CampaignExecutionHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

class CampaignQueuedPayloadExecutionHandoffMapper implements MapsCampaignQueuedPayloadsToExecutionHandoffs
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignExecutionHandoffWorkspaceInputData
    {
        if ($payload->operation !== 'execution.handoff') {
            throw new InvalidArgumentException("Queued campaign payload operation [{$payload->operation}] cannot be mapped to an execution handoff.");
        }

        $executionId = $payload->payload['execution_id'] ?? null;

        if (! is_scalar($executionId) || trim((string) $executionId) === '') {
            throw new InvalidArgumentException('Queued execution handoff payloads require an execution_id.');
        }

        $requestedBy = $payload->metadata['operator_id'] ?? $payload->metadata['requested_by'] ?? 'queue';

        return new CampaignExecutionHandoffWorkspaceInputData(
            planningKey: $payload->planningKey,
            executionId: (string) $executionId,
            requestedBy: is_scalar($requestedBy) && trim((string) $requestedBy) !== '' ? (string) $requestedBy : 'queue',
            correlationId: $payload->correlationId,
            metadata: [
                ...$payload->metadata,
                'operation' => $payload->operation,
                'source' => 'queued-payload-execution-handoff-mapper',
                'handoff_only' => true,
            ],
        );
    }
}
