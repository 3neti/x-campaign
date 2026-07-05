<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\RemovesRecipientsFromCampaignAudiencePlans;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

class RemoveRecipientFromCampaignAudiencePlan implements RemovesRecipientsFromCampaignAudiencePlans
{
    public function handle(CampaignPlanData $plan, string $audienceId, string $recipientReference): CampaignPlanData
    {
        $matched = false;

        $audiences = array_map(function (CampaignAudiencePlanData $audience) use ($audienceId, $recipientReference, &$matched): CampaignAudiencePlanData {
            if ($audience->audience->id !== $audienceId) {
                return $audience;
            }

            $matched = true;

            return new CampaignAudiencePlanData(
                audience: $audience->audience,
                recipients: array_values(array_filter(
                    $audience->recipients,
                    fn (CampaignRecipientData $recipient): bool => ! $this->matches($recipient, $recipientReference),
                )),
                effects: [
                    ...$audience->effects,
                    'persists' => false,
                    'deletes_records' => false,
                ],
                metadata: [
                    ...$audience->metadata,
                    'recipient_removed_in_memory' => true,
                ],
            );
        }, $plan->audiences);

        if (! $matched) {
            throw new InvalidArgumentException("Unknown campaign audience plan [{$audienceId}].");
        }

        return new CampaignPlanData(
            campaign: $plan->campaign,
            audiences: $audiences,
            executions: $plan->executions,
            effects: [
                ...$plan->effects,
                'persists' => false,
                'deletes_records' => false,
            ],
            metadata: [
                ...$plan->metadata,
                'recipient_removed_in_memory' => true,
            ],
        );
    }

    private function matches(CampaignRecipientData $recipient, string $reference): bool
    {
        return in_array($reference, array_filter([
            $recipient->id,
            $recipient->externalReference,
            $recipient->mobile,
            $recipient->email,
        ]), true);
    }
}
