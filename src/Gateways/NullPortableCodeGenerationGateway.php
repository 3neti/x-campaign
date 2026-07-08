<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Gateways;

use LBHurtado\XCampaign\Contracts\PayCodeGenerationGateway;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;

class NullPortableCodeGenerationGateway implements PayCodeGenerationGateway
{
    /**
     * @return array<string, mixed>
     */
    public function generate(CampaignExecutionData $execution, CampaignRecipientData $recipient): array
    {
        return [
            'status' => 'planned',
            'generation_id' => $this->generationId($execution, $recipient),
            'portable_code_reference' => null,
            'provider_reference' => null,
            'metadata' => [
                'gateway' => 'null',
                'issued' => false,
                'handoff_only' => true,
            ],
            'effects' => (new CampaignPersistenceEffectData)->toArray(),
        ];
    }

    private function generationId(CampaignExecutionData $execution, CampaignRecipientData $recipient): string
    {
        return 'generation-'.substr(hash('sha256', implode('|', [
            $execution->id,
            $recipient->id,
        ])), 0, 16);
    }
}
