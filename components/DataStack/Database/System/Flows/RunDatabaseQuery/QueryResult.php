<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseQuery;

final readonly class QueryResult
{
    public function __construct(
        public array $rows = [],
        public int $affected = 0,
    ) {
    }

    public function first(): ?array
    {
        return $this->rows[0] ?? null;
    }
}
