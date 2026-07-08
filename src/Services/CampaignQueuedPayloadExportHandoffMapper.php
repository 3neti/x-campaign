<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExportHandoffs;
use LBHurtado\XCampaign\Data\CampaignExportHandoffWorkspaceInputData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

class CampaignQueuedPayloadExportHandoffMapper implements MapsCampaignQueuedPayloadsToExportHandoffs
{
    public function map(CampaignQueuedPlanPayloadData $payload): CampaignExportHandoffWorkspaceInputData
    {
        if ($payload->operation !== 'report.export') {
            throw new InvalidArgumentException("Queued campaign payload operation [{$payload->operation}] cannot be mapped to export handoff.");
        }

        $executionId = $payload->payload['execution_id'] ?? null;

        if (! is_scalar($executionId) || trim((string) $executionId) === '') {
            throw new InvalidArgumentException('Queued export handoff payloads require an execution_id.');
        }

        return new CampaignExportHandoffWorkspaceInputData(
            planningKey: $payload->planningKey,
            executionId: (string) $executionId,
            reportType: $this->stringValue($payload->payload['report_type'] ?? null, 'operator_summary'),
            format: $this->stringValue($payload->payload['format'] ?? null, 'csv'),
            destination: $this->stringValue($payload->payload['destination'] ?? null, 'operator_download'),
            correlationId: $payload->correlationId,
            metadata: [
                ...$payload->metadata,
                'operation' => $payload->operation,
                'source' => 'queued-payload-export-handoff-mapper',
                'export_handoff_only' => true,
            ],
        );
    }

    private function stringValue(mixed $value, string $fallback): string
    {
        return is_scalar($value) && trim((string) $value) !== ''
            ? (string) $value
            : $fallback;
    }
}
