<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Values;

/**
 * Immutable result of a database query execution.
 */
final readonly class QueryResult
{
    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(
        public array $rows = [],
        public int $affectedRows = 0,
        public ?string $lastInsertId = null,
        public float $elapsedMs = 0.0,
    ) {}

    /**
     * @param list<array<string, mixed>> $rows
     */
    public static function select(array $rows, float $elapsedMs = 0.0) : self
    {
        return new self(rows: $rows, elapsedMs: $elapsedMs);
    }

    public static function write(int $affectedRows = 0, ?string $lastInsertId = null, float $elapsedMs = 0.0) : self
    {
        return new self(affectedRows: $affectedRows, lastInsertId: $lastInsertId, elapsedMs: $elapsedMs);
    }

    public function isRead() : bool
    {
        return $this->rows !== [];
    }
}
