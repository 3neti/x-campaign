<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignClaimVisibilities;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityData;
use LBHurtado\XCampaign\Data\CampaignClaimVisibilityResultData;

class PlanCampaignClaimVisibility implements PlansCampaignClaimVisibilities
{
    public function plan(CampaignClaimVisibilityData $visibility): CampaignClaimVisibilityResultData
    {
        $planningKey = trim($visibility->planningKey);

        if ($planningKey === '') {
            throw new InvalidArgumentException('Claim visibility planning key is required.');
        }

        $blockers = $this->blockers($visibility);

        return new CampaignClaimVisibilityResultData(
            status: $blockers === [] ? 'visible' : 'blocked',
            visibilityId: $this->visibilityId($visibility),
            visibility: $visibility,
            blockers: $blockers,
            metadata: [
                ...$visibility->metadata,
                'planning_key' => $planningKey,
                'campaign_id' => $visibility->execution->campaignId,
                'audience_id' => $visibility->execution->audienceId,
                'execution_id' => $visibility->execution->id,
                'recipient_id' => $visibility->recipient->id,
                'generation_id' => $visibility->generationResult->generationId,
                'portable_code_reference' => $visibility->generationResult->portableCodeReference,
                'claim_status' => $this->claimStatus($visibility),
                'requested_by' => $visibility->requestedBy,
                'correlation_id' => $visibility->correlationId,
                'visibility_only' => true,
                'claim_runtime_invoked' => false,
                'source' => 'in-memory-claim-visibility-planning',
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignClaimVisibilityData $visibility): array
    {
        $blockers = [];

        if ($visibility->generationResult->portableCodeReference === null || trim($visibility->generationResult->portableCodeReference) === '') {
            $blockers[] = 'Claim visibility requires a portable-code reference.';
        }

        if ($visibility->claimStatus === []) {
            $blockers[] = 'Claim visibility requires a claim status snapshot.';
        }

        return $blockers;
    }

    private function claimStatus(CampaignClaimVisibilityData $visibility): string
    {
        $status = $visibility->claimStatus['status'] ?? 'unknown';

        return is_scalar($status) && trim((string) $status) !== '' ? (string) $status : 'unknown';
    }

    private function visibilityId(CampaignClaimVisibilityData $visibility): string
    {
        return 'claim-visibility-'.substr(hash('sha256', implode('|', [
            trim($visibility->planningKey),
            $visibility->execution->id,
            $visibility->recipient->id,
            $visibility->generationResult->generationId,
            $visibility->generationResult->portableCodeReference,
            $this->claimStatus($visibility),
        ])), 0, 16);
    }
}
