<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;

class ProcessCampaignQueuedPlan implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly CampaignQueuedPlanPayloadData $queuedPayload,
    ) {
        $this->onQueue('campaigns');
    }

    public function handle(): void {}

    public function payload(): CampaignQueuedPlanPayloadData
    {
        return $this->queuedPayload;
    }

    public function effects(): CampaignPersistenceEffectData
    {
        return new CampaignPersistenceEffectData;
    }
}
