<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\AddsRecipientsToCampaignAudiencePlans;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignPlanData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

class AddRecipientToCampaignAudiencePlan implements AddsRecipientsToCampaignAudiencePlans
{
    public function handle(CampaignPlanData $plan, string $audienceId, CampaignRecipientPlanningInputData $input): CampaignPlanData
    {
        $matched = false;
        $audiences = array_map(function (CampaignAudiencePlanData $audience) use ($audienceId, $input, &$matched): CampaignAudiencePlanData {
            if ($audience->audience->id !== $audienceId) {
                return $audience;
            }

            $matched = true;

            return new CampaignAudiencePlanData(
                audience: $audience->audience,
                recipients: [
                    ...$audience->recipients,
                    $this->recipient($input),
                ],
                effects: [
                    ...$audience->effects,
                    ...$this->effects(),
                ],
                metadata: [
                    ...$audience->metadata,
                    'recipient_planning' => 'in-memory',
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
                ...$this->effects(),
            ],
            metadata: [
                ...$plan->metadata,
                'recipient_planning' => 'in-memory',
            ],
        );
    }

    private function recipient(CampaignRecipientPlanningInputData $input): CampaignRecipientData
    {
        return new CampaignRecipientData(
            id: $input->id,
            name: $this->nullableTrim($input->name),
            mobile: $this->normalizeMobile($input->mobile),
            email: $this->normalizeEmail($input->email),
            address: $this->nullableTrim($input->address),
            externalReference: $this->nullableTrim($input->externalReference),
            metadata: $input->metadata,
        );
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'persists' => false,
            'imports_files' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }

    private function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeEmail(?string $value): ?string
    {
        $trimmed = $this->nullableTrim($value);

        return $trimmed === null ? null : strtolower($trimmed);
    }

    private function normalizeMobile(?string $value): ?string
    {
        $trimmed = $this->nullableTrim($value);

        return $trimmed === null ? null : str_replace(' ', '', $trimmed);
    }
}
