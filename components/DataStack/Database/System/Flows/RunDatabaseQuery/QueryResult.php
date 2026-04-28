<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseQuery;

final class QueryResult
{
    public function __construct(
        public readonly array $rows = [],
        public readonly int   $affected = 0,
    ) {}

    public function first() : array|null
    {
        return $this->rows[0] ?? null;
    }
}