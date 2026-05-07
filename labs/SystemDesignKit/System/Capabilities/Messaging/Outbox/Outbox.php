<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Outbox;

/**
 * Outbox pattern model.
 *
 * @experimental V3 labs
 *
 * Models the outbox pattern: writes are saved to an outbox table
 * in the same transaction as the business data, then relayed
 * to the message broker asynchronously.
 */
final readonly class Outbox
{
    public function __construct(
        public bool $enabled,
        public int  $pollIntervalMs,
        public int  $maxBatchSize,
        public int  $retentionHours,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if (! $this->enabled) {
            return ['valid' => true, 'errors' => []];
        }

        if ($this->pollIntervalMs <= 0) {
            $errors[] = 'poll_interval_ms must be positive.';
        }

        if ($this->maxBatchSize < 1) {
            $errors[] = 'max_batch_size must be >= 1.';
        }

        if ($this->retentionHours < 1) {
            $errors[] = 'retention_hours must be >= 1.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Estimated maximum relay lag (poll interval + processing time).
     */
    public function estimatedRelayLagMs() : int
    {
        if (! $this->enabled) {
            return 0;
        }

        return $this->pollIntervalMs + 50;
    }
}
