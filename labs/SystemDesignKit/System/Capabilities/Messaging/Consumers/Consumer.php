<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Consumers;

/**
 * Consumer model — throughput, partition, and lag characteristics.
 *
 * @experimental V3 labs
 */
final readonly class Consumer
{
    public function __construct(
        public string $name,
        public int    $throughputPerSecond,
        public int    $partitionCount,
        public bool   $ordered,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->name === '') {
            $errors[] = 'consumer name must not be empty.';
        }

        if ($this->throughputPerSecond < 1) {
            $errors[] = 'throughput_per_second must be >= 1.';
        }

        if ($this->partitionCount < 1) {
            $errors[] = 'partition_count must be >= 1.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Total throughput across all partitions.
     */
    public function totalThroughput() : int
    {
        return $this->throughputPerSecond * $this->partitionCount;
    }
}
