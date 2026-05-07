<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Queue;

/**
 * Consumer throughput model.
 *
 * @experimental V3 labs
 */
final readonly class ConsumerThroughput
{
    public function __construct(
        public int $perSecond,
        public int $consumerCount = 1,
    ) {}

    /**
     * Total system throughput (all consumers combined).
     */
    public function totalThroughput() : int
    {
        return $this->perSecond * $this->consumerCount;
    }

    /**
     * Required consumer count to handle given input rate.
     */
    public function requiredConsumers(int $inputPerSecond) : int
    {
        if ($this->perSecond === 0) {
            return PHP_INT_MAX;
        }

        return (int) ceil($inputPerSecond / $this->perSecond);
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->perSecond <= 0) {
            $errors[] = 'consumer_throughput_per_second must be positive.';
        }

        if ($this->consumerCount < 1) {
            $errors[] = 'consumer_count must be >= 1.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
