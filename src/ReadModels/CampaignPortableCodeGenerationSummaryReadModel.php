<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\ReadModels;

use LBHurtado\XCampaign\Contracts\BuildsCampaignPortableCodeGenerationSummaries;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationSummaryData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationWorkspaceResultData;

class CampaignPortableCodeGenerationSummaryReadModel implements BuildsCampaignPortableCodeGenerationSummaries
{
    public function fromWorkspaceResult(CampaignPortableCodeGenerationWorkspaceResultData $result): CampaignPortableCodeGenerationSummaryData
    {
        return new CampaignPortableCodeGenerationSummaryData(
            status: $result->status,
            planningKey: $result->planningKey,
            executionId: $result->executionId,
            plannedCount: $this->countStatus($result, 'planned'),
            blockedCount: $this->countBlocked($result),
            recipientCount: $this->recipientCount($result),
            ready: $result->status === 'planned' && $result->blockers === [],
            blockers: $result->blockers,
            effects: $result->effects->toArray(),
            metadata: [
                ...$result->metadata,
                'source' => 'portable-code-generation-summary-read-model',
                'read_only' => true,
            ],
        );
    }

    private function countStatus(CampaignPortableCodeGenerationWorkspaceResultData $result, string $status): int
    {
        return array_reduce(
            $result->generationResults,
            fn (int $total, mixed $generationResult): int => $total + (
                $generationResult instanceof CampaignPortableCodeGenerationResultData && $generationResult->status === $status ? 1 : 0
            ),
            0,
        );
    }

    private function countBlocked(CampaignPortableCodeGenerationWorkspaceResultData $result): int
    {
        return array_reduce(
            $result->generationResults,
            fn (int $total, mixed $generationResult): int => $total + (
                $generationResult instanceof CampaignPortableCodeGenerationResultData && $generationResult->status !== 'planned' ? 1 : 0
            ),
            0,
        );
    }

    private function recipientCount(CampaignPortableCodeGenerationWorkspaceResultData $result): int
    {
        $count = $result->metadata['recipient_count'] ?? null;

        return is_int($count) ? $count : count($result->generationResults);
    }
}
