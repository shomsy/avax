<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Broker;

/**
 * Broker topology model.
 *
 * @experimental V3 labs
 *
 * Models the message broker type, partition count,
 * and replication factor at the architecture level.
 */
final readonly class Broker
{
    public function __construct(
        public string $brokerType,
        public int    $partitionCount,
        public int    $replicationFactor,
        public string $deliverySemantics,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->brokerType === '') {
            $errors[] = 'broker_type must not be empty.';
        }

        if ($this->partitionCount < 1) {
            $errors[] = 'partition_count must be >= 1.';
        }

        if ($this->replicationFactor < 1) {
            $errors[] = 'replication_factor must be >= 1.';
        }

        $validSemantics = ['at_most_once', 'at_least_once', 'exactly_once_illusion'];

        if (! in_array($this->deliverySemantics, $validSemantics, true)) {
            $errors[] = "delivery_semantics must be one of: " . implode(', ', $validSemantics) . '.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
