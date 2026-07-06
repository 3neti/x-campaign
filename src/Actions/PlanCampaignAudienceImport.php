<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImports;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanData;
use LBHurtado\XCampaign\Data\CampaignAudienceImportPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignImportData;

class PlanCampaignAudienceImport implements PlansCampaignAudienceImports
{
    public function handle(CampaignAudienceImportPlanningInputData $input): CampaignAudienceImportPlanData
    {
        $audienceId = $this->audienceId($input->audienceId);

        return new CampaignAudienceImportPlanData(
            import: new CampaignImportData(
                id: $this->nullableString($input->id),
                audienceId: $audienceId,
                source: $this->source($input->source),
                status: 'planned',
                recipientCount: max(0, $input->expectedRecipientCount),
                metadata: $input->metadata,
            ),
            effects: $this->effects(),
            metadata: [
                'source_reference' => $this->nullableString($input->sourceReference),
                'columns' => array_values($input->columns),
                'planning_only' => true,
            ],
        );
    }

    private function audienceId(?string $audienceId): string
    {
        $audienceId = $this->nullableString($audienceId);

        if ($audienceId === null) {
            throw new InvalidArgumentException('Campaign audience import planning requires an audience id.');
        }

        return $audienceId;
    }

    private function source(string $source): string
    {
        $source = trim($source);

        return $source === '' ? 'manual' : $source;
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, bool>
     */
    private function effects(): array
    {
        return [
            'parses_files' => false,
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ];
    }
}

