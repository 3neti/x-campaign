<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToClaimVisibilities;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

class CampaignQueuedPayloadClaimVisibilityMapper implements MapsCampaignQueuedPayloadsToClaimVisibilities
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignClaimVisibilityWorkspaceInputData
    {
        if ($payload->operation !== 'claim.visibility') {
            throw new InvalidArgumentException("Queued campaign payload operation [{$payload->operation}] cannot be mapped to claim visibility.");
        }

        $executionId = $payload->payload['execution_id'] ?? null;

        if (! is_scalar($executionId) || trim((string) $executionId) === '') {
            throw new InvalidArgumentException('Queued claim visibility payloads require an execution_id.');
        }

        $requestedBy = $payload->metadata['operator_id'] ?? $payload->metadata['requested_by'] ?? 'queue';

        return new CampaignClaimVisibilityWorkspaceInputData(
            planningKey: $payload->planningKey,
            executionId: (string) $executionId,
            requestedBy: is_scalar($requestedBy) && trim((string) $requestedBy) !== '' ? (string) $requestedBy : 'queue',
            correlationId: $payload->correlationId,
            metadata: [
                ...$payload->metadata,
                'operation' => $payload->operation,
                'source' => 'queued-payload-claim-visibility-mapper',
                'visibility_only' => true,
            ],
        );
    }
}
