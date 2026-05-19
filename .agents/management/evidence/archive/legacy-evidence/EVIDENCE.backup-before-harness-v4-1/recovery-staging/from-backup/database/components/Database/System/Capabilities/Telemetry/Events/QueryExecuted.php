<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Telemetry\Events;

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

    /** @var bool A checkmark: "Did we hide the private info?" */
    public bool $bindingsRedacted;

    /** @var array<array-key, mixed> THE "Raw" original values (Hidden from logs). */
    public array $rawBindings;

    public string $connectionName;

    public float $timeMs;

    public string $sql;

    /**
     * @param string $sql            The actual SQL text that was run.
     * @param array  $bindings       The "Fill-in-the-blank" values used.
     * @param float  $timeMs         How many milliseconds it took to finish.
     * @param string $connectionName The nickname of the database used.
     * @param string $correlationId  The Trace ID (Luggage Tag) for this request.
     * @param bool   $redactBindings Should we use the "Black Marker" to hide values in the main report?
     */
    public function __construct(
        string                      $sql,
        #[SensitiveParameter] array $bindings,
        float                       $timeMs,
        string                      $connectionName,
        string                      $correlationId,
        bool                        $redactBindings = true
    )
    {
        $this->sql              = $sql;
        $this->timeMs           = $timeMs;
        $this->connectionName   = $connectionName;
        $this->rawBindings      = $bindings;
        $this->redactedBindings = self::redactBindings(bindings: $bindings);
        $this->bindingsRedacted = $redactBindings;
        $this->bindings         = $redactBindings ? $this->redactedBindings : $this->rawBindings;
        parent::__construct(correlationId: $correlationId);
    }

    /**
     * Redact sensitive query parameters for telemetry.
     */
    private static function redactBindings(array $bindings) : array
    {
        return array_map(callback: static fn ($value) => '[REDACTED]', array: $bindings);
    }
}
