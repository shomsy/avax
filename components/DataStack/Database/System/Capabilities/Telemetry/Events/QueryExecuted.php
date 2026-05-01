<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events;

use SensitiveParameter;

/**
 * Telemetry event emitted after a database query execution.
 *
 * @see /docs/Foundation/Database/Concepts/Telemetry.md#queryexecuted
 */
final readonly class QueryExecuted extends Event
{
    /** @var array<array-key, mixed> THE values sent with the query (Safe or Redacted version). */
    public array $bindings;

    /** @var array<array-key, string> THE "Blacked-out" version of the values for public logs. */
    public array $redactedBindings;

    /**
     * @param string $sql            The actual SQL text that was run.
     * @param array $rawBindings      The "Fill-in-the-blank" values used.
     * @param float  $timeMs         How many milliseconds it took to finish.
     * @param string $connectionName The nickname of the database used.
     * @param string $correlationId  The Trace ID (Luggage Tag) for this request.
     * @param bool  $bindingsRedacted Should we use the "Black Marker" to hide values in the main report?
     */
    public function __construct(
        public string $sql,
        #[SensitiveParameter]
        public array  $rawBindings,
        public float  $timeMs,
        public string $connectionName,
        string $correlationId,
        public bool   $bindingsRedacted = true,
    ) {
        $this->redactedBindings = $this->redactBindings(bindings: $this->rawBindings);
        $this->bindings         = $this->bindingsRedacted ? $this->redactedBindings : $this->rawBindings;
        parent::__construct(correlationId: $correlationId);
    }

    /**
     * Redact sensitive query parameters for telemetry.
     */
    private function redactBindings(array $bindings) : array
    {
        return array_map(callback: static fn ($value) : string => '[REDACTED]', array: $bindings);
    }
}
