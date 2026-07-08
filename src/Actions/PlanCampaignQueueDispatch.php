<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Actions;

use InvalidArgumentException;
use LBHurtado\XCampaign\Contracts\PlansCampaignQueueDispatches;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchData;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchResultData;

class PlanCampaignQueueDispatch implements PlansCampaignQueueDispatches
{
    public function plan(CampaignQueueDispatchData $dispatch): CampaignQueueDispatchResultData
    {
        $this->guardCompleteIntent($dispatch);

        return new CampaignQueueDispatchResultData(
            status: 'planned',
            dispatchId: $this->dispatchId($dispatch),
            dispatch: $dispatch,
            metadata: [
                ...$dispatch->metadata,
                'planning_key' => $dispatch->planningKey,
                'job' => $dispatch->job,
                'queue' => $dispatch->queue,
                'connection' => $dispatch->connection,
                'planned_only' => true,
            ],
        );
    }

    private function guardCompleteIntent(CampaignQueueDispatchData $dispatch): void
    {
        if (trim($dispatch->planningKey) === '') {
            throw new InvalidArgumentException('Campaign queue planning requires a planning key.');
        }

        if (trim($dispatch->job) === '') {
            throw new InvalidArgumentException('Campaign queue planning requires a job key.');
        }

        if (trim($dispatch->queue) === '') {
            throw new InvalidArgumentException('Campaign queue planning requires a queue name.');
        }
    }

    private function dispatchId(CampaignQueueDispatchData $dispatch): string
    {
        $fingerprint = json_encode([
            'planning_key' => $dispatch->planningKey,
            'job' => $dispatch->job,
            'queue' => $dispatch->queue,
            'connection' => $dispatch->connection,
            'payload' => $dispatch->payload,
        ], JSON_THROW_ON_ERROR);

        return 'queue-plan-'.substr(hash('sha256', $fingerprint), 0, 24);
    }
}
