<?php

declare(strict_types=1);

namespace Avax\Database\Support;

use Random\RandomException;

/**
 * Metadata container for correlating disparate database operations within a single logical request.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Telemetry.md
 */
final readonly class ExecutionScope
{
    /**
     * @param  string  $correlationId  The unique "Trace ID" for this specific run.
     * @param  array  $metadata  Any extra notes you want to carry with the query for logging.
     */
    public function __construct(
        public string $correlationId,
        public array $metadata = []
    ) {}

    /**
     * Create a new scope with a randomly generated correlation ID.
     *
     * @param  array  $metadata  Initial metrics/context data.
     *
     * @throws RandomException
     */
    public static function fresh(array $metadata = []): self
    {
        $id = bin2hex(string: random_bytes(length: 8));

        return new self(correlationId: $id, metadata: $metadata);
    }
}
