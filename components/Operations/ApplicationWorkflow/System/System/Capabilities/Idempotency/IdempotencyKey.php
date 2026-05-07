<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\Idempotency;

use InvalidArgumentException;

/**
 * Generates and manages idempotency keys for saga step executions.
 * Ensures that a step is only executed once per saga instance.
 */
final readonly class IdempotencyKey
{
    public function __construct(
        public string $sagaId,
        public string $stepName,
        public string $attempt,
    ) {}

    /**
     * Generate a unique idempotency key for a step execution.
     */
    public static function generate(string $sagaId, string $stepName, string $attempt = '0') : self
    {
        return new self(
            sagaId  : $sagaId,
            stepName: $stepName,
            attempt : $attempt,
        );
    }

    /**
     * Parse a string representation back into an IdempotencyKey.
     */
    public static function fromString(string $key) : self
    {
        $parts = explode(':', $key, 3);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Invalid idempotency key format');
        }

        return new self(
            sagaId  : $parts[0],
            stepName: $parts[1],
            attempt : $parts[2],
        );
    }

    /**
     * Get the string representation of the key.
     */
    public function toString() : string
    {
        return sprintf('%s:%s:%s', $this->sagaId, $this->stepName, $this->attempt);
    }
}
