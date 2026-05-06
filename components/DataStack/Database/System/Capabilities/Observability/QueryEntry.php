<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Observability;

/**
 * A single recorded query execution entry in the timeline.
 *
 * Readonly value object capturing a query's execution details.
 */
final readonly class QueryEntry
{
    public function __construct(
        public string $sql,
        public array $bindings = [],
        public float $timestamp = 0.0,
        public float $durationMs = 0.0,
        public string $type = 'unknown',
        public string $connection = '',
        public int $affectedRows = 0,
        public ?string $error = null,
    ) {
    }

    /**
     * Creates a QueryEntry with the current timestamp.
     */
    public static function create(
        string $sql,
        array $bindings = [],
        float $durationMs = 0.0,
        string $type = 'unknown',
        string $connection = '',
        int $affectedRows = 0,
        ?string $error = null,
    ): self {
        return new self(
            sql: $sql,
            bindings: $bindings,
            timestamp: microtime(true),
            durationMs: $durationMs,
            type: $type,
            connection: $connection,
            affectedRows: $affectedRows,
            error: $error,
        );
    }

    /**
     * Checks if this query resulted in an error.
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Returns a formatted duration string.
     */
    public function formattedDuration(): string
    {
        if ($this->durationMs < 1) {
            return number_format($this->durationMs * 1000, 2).'μs';
        }

        if ($this->durationMs < 1000) {
            return number_format($this->durationMs, 2).'ms';
        }

        return number_format($this->durationMs / 1000, 2).'s';
    }

    /**
     * Converts to an associative array.
     */
    public function toArray(): array
    {
        return [
            'sql' => $this->sql,
            'bindings' => $this->bindings,
            'timestamp' => $this->timestamp,
            'duration_ms' => $this->durationMs,
            'type' => $this->getType(),
            'connection' => $this->connection,
            'affected_rows' => $this->affectedRows,
            'error' => $this->error,
        ];
    }

    /**
     * Returns the query type (SELECT, INSERT, UPDATE, DELETE, etc.).
     */
    public function getType(): string
    {
        if ($this->type !== 'unknown') {
            return $this->type;
        }

        $trimmed = ltrim($this->sql);

        return strtoupper(substr($trimmed, 0, 6));
    }
}
