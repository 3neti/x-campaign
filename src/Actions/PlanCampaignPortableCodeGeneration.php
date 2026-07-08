<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignPortableCodeGenerations;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationRequestData;
use LBHurtado\XCampaign\Data\CampaignPortableCodeGenerationResultData;

class PlanCampaignPortableCodeGeneration implements PlansCampaignPortableCodeGenerations
{
    public function plan(CampaignPortableCodeGenerationRequestData $request): CampaignPortableCodeGenerationResultData
    {
        $planningKey = trim($request->planningKey);

        if ($planningKey === '') {
            throw new InvalidArgumentException('Portable code generation planning key is required.');
        }

        $blockers = $this->blockers($request);

        return new CampaignPortableCodeGenerationResultData(
            status: $blockers === [] ? 'planned' : 'blocked',
            generationId: $this->generationId($request),
            request: $request,
            blockers: $blockers,
            metadata: [
                'planning_key' => $planningKey,
                'campaign_id' => $request->execution->campaignId,
                'audience_id' => $request->execution->audienceId,
                'execution_id' => $request->execution->id,
                'recipient_id' => $request->recipient->id,
                'batch_id' => $request->batchId,
                'correlation_id' => $request->correlationId,
                'source' => 'portable-code-generation-planner',
                'gateway_invoked' => false,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignPortableCodeGenerationRequestData $request): array
    {
        return $request->recipient->id === null || trim($request->recipient->id) === ''
            ? ['Portable code generation requires a recipient identifier.']
            : [];
    }

    private function generationId(CampaignPortableCodeGenerationRequestData $request): string
    {
        return 'generation-'.substr(hash('sha256', implode('|', [
            trim($request->planningKey),
            $request->execution->id,
            $request->recipient->id,
        ])), 0, 16);
    }
}
