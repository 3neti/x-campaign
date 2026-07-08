<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Queue;

use Illuminate\Support\Facades\Queue;
use LBHurtado\XCampaign\Contracts\DispatchesCampaignQueuedPlans;
use LBHurtado\XCampaign\Data\CampaignPersistenceEffectData;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchData;
use LBHurtado\XCampaign\Data\CampaignQueueDispatchResultData;
use LBHurtado\XCampaign\Data\CampaignQueuedPlanPayloadData;
use LBHurtado\XCampaign\Jobs\ProcessCampaignQueuedPlan;

class CampaignQueueDispatcher implements DispatchesCampaignQueuedPlans
{
    public function dispatch(
        CampaignQueuedPlanPayloadData $payload,
        ?string $queue = null,
        ?string $connection = null,
    ): CampaignQueueDispatchResultData {
        $queueName = $queue ?? 'campaigns';
        $job = new ProcessCampaignQueuedPlan($payload);

        if ($connection !== null && trim($connection) !== '') {
            $job->onConnection($connection);
            Queue::connection($connection)->pushOn($queueName, $job);
        } else {
            Queue::pushOn($queueName, $job);
        }

        $dispatch = new CampaignQueueDispatchData(
            planningKey: $payload->planningKey,
            job: ProcessCampaignQueuedPlan::class,
            payload: $payload->toArray(),
            queue: $queueName,
            connection: $connection,
            metadata: [
                'correlation_id' => $payload->correlationId,
                'operation' => $payload->operation,
            ],
            effects: new CampaignPersistenceEffectData(queuesJobs: true),
        );

        return new CampaignQueueDispatchResultData(
            status: 'queued',
            dispatchId: 'queue-'.substr(hash('sha256', json_encode($dispatch->toArray(), JSON_THROW_ON_ERROR)), 0, 24),
            dispatch: $dispatch,
            metadata: [
                'queued' => true,
                'correlation_id' => $payload->correlationId,
                'operation' => $payload->operation,
                'queue' => $queueName,
                'connection' => $connection,
            ],
            effects: new CampaignPersistenceEffectData(queuesJobs: true),
        );
    }
}
