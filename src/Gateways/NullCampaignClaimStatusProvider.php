<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Gateways;

use LBHurtado\XCampaign\Contracts\CampaignClaimStatusProvider;

class NullCampaignClaimStatusProvider implements CampaignClaimStatusProvider
{
    /**
     * @return array<string, mixed>
     */
    public function status(string $payCode): array
    {
        return [
            'status' => 'unknown',
            'pay_code' => $payCode,
            'source' => 'null-campaign-claim-status-provider',
            'claim_runtime_invoked' => false,
        ];
    }
}
