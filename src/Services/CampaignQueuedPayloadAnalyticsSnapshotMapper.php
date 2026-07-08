<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToAnalyticsSnapshots;
use LBHurtado\XCampaign\Data\CampaignAnalyticsWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

class CampaignQueuedPayloadAnalyticsSnapshotMapper implements MapsCampaignQueuedPayloadsToAnalyticsSnapshots
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignAnalyticsWorkspaceInputData
    {
        if ($payload->operation !== 'analytics.snapshot') {
            throw new InvalidArgumentException("Queued campaign payload operation [{$payload->operation}] cannot be mapped to analytics snapshots.");
        }

        $executionId = $payload->payload['execution_id'] ?? null;

        if (! is_scalar($executionId) || trim((string) $executionId) === '') {
            throw new InvalidArgumentException('Queued analytics snapshot payloads require an execution_id.');
        }

        $channel = $payload->payload['channel'] ?? 'sms';

        return new CampaignAnalyticsWorkspaceInputData(
            planningKey: $payload->planningKey,
            executionId: (string) $executionId,
            channel: is_scalar($channel) && trim((string) $channel) !== '' ? (string) $channel : 'sms',
            correlationId: $payload->correlationId,
            metadata: [
                ...$payload->metadata,
                'operation' => $payload->operation,
                'source' => 'queued-payload-analytics-snapshot-mapper',
                'analytics_only' => true,
            ],
        );
    }
}
