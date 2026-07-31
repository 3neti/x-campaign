<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignDeliveryHandoffs;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffData;
use LBHurtado\XCampaign\Data\CampaignDeliveryHandoffResultData;

class PlanCampaignDeliveryHandoff implements PlansCampaignDeliveryHandoffs
{
    public function plan(CampaignDeliveryHandoffData $handoff): CampaignDeliveryHandoffResultData
    {
        $planningKey = trim($handoff->planningKey);

        if ($planningKey === '') {
            throw new InvalidArgumentException('Delivery handoff planning key is required.');
        }

        $blockers = $this->blockers($handoff);

        return new CampaignDeliveryHandoffResultData(
            status: $blockers === [] ? 'ready' : 'blocked',
            handoffId: $this->handoffId($handoff),
            handoff: $handoff,
            blockers: $blockers,
            metadata: [
                ...$handoff->metadata,
                'planning_key' => $planningKey,
                'campaign_id' => $handoff->execution->campaignId,
                'audience_id' => $handoff->execution->audienceId,
                'execution_id' => $handoff->execution->id,
                'recipient_id' => $handoff->recipient->id,
                'generation_id' => $handoff->generationResult->generationId,
                'channel' => trim($handoff->channel),
                'requested_by' => $handoff->requestedBy,
                'correlation_id' => $handoff->correlationId,
                'handoff_only' => true,
                'delivery_invoked' => false,
                'source' => 'in-memory-delivery-handoff-planning',
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    private function blockers(CampaignDeliveryHandoffData $handoff): array
    {
        $blockers = [];

        if ($handoff->generationResult->status !== 'planned') {
            $blockers[] = 'Delivery handoff requires planned portable-code generation.';
        }

        if ($handoff->generationResult->portableCodeReference === null || trim($handoff->generationResult->portableCodeReference) === '') {
            $blockers[] = 'Delivery handoff requires a portable-code reference.';
        }

        if (! $this->hasChannelContact($handoff)) {
            $blockers[] = match (strtolower(trim($handoff->channel))) {
                'email' => 'Delivery handoff requires a recipient email address for email channel.',
                'webhook' => 'Delivery handoff requires a recipient external reference for webhook channel.',
                default => 'Delivery handoff requires a recipient mobile number for sms channel.',
            };
        }

        return $blockers;
    }

    private function hasChannelContact(CampaignDeliveryHandoffData $handoff): bool
    {
        return match (strtolower(trim($handoff->channel))) {
            'email' => $handoff->recipient->email !== null && trim($handoff->recipient->email) !== '',
            'webhook' => $handoff->recipient->externalReference !== null && trim($handoff->recipient->externalReference) !== '',
            default => $handoff->recipient->mobile !== null && trim($handoff->recipient->mobile) !== '',
        };
    }

    private function handoffId(CampaignDeliveryHandoffData $handoff): string
    {
        return 'delivery-handoff-'.substr(hash('sha256', implode('|', [
            trim($handoff->planningKey),
            $handoff->execution->id,
            $handoff->recipient->id,
            $handoff->generationResult->generationId,
            trim($handoff->channel),
        ])), 0, 16);
    }
}
