<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Contracts;

interface CampaignClaimStatusProvider
{
    /**
     * @return array<string, mixed>
     */
    public function status(string $payCode): array;
}
