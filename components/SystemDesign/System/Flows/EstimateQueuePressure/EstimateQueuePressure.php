<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\EstimateQueuePressure;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;

/**
 * Estimates queue depth and consumer throughput.
 *
 * @experimental V3 labs
 */
final class EstimateQueuePressure
{
    /**
     * @return array{
     *     write_rps: int,
     *     consumer_throughput: int,
     *     consumer_count: int,
     *     required_consumers: int,
     *     can_handle_load: bool,
     *     queue_utilization: float,
     *     consumers_underprovisioned: bool,
     * }
     */
    public function execute(CapacityModel $model) : array
    {
        $requiredConsumers = $model->requiredConsumerCount();
        $canHandle         = $model->canHandleWriteLoad();

        return [
            'write_rps'                  => $model->traffic->writes,
            'consumer_throughput'        => $model->consumerThroughput->perSecond,
            'consumer_count'             => $model->consumerThroughput->consumerCount,
            'required_consumers'         => $requiredConsumers,
            'can_handle_load'            => $canHandle,
            'queue_utilization'          => $model->queueDepth->utilizationRatio(),
            'consumers_underprovisioned' => $requiredConsumers > $model->consumerThroughput->consumerCount,
        ];
    }
}
