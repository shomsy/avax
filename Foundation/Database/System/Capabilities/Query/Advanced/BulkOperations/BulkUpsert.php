<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\BulkOperations;

use Avax\Database\System\Capabilities\Query\Advanced\Upsert\OnConflict;
use Avax\Database\System\Capabilities\Query\Advanced\Upsert\UpsertBuilder;
use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class BulkUpsert
{
    public function __construct(
        private readonly GrammarInterface $grammar,
        private readonly string           $table,
        private readonly array            $columns,
        private readonly OnConflict       $conflict,
        private readonly int              $batchSize = 100,
    ) {}

    /**
     * @return list<array{sql: string, bindings: list<mixed>}>
     */
    public function build(array $rows) : array
    {
        $statements = [];

        foreach (array_chunk(array: $rows, size: $this->batchSize) as $batch) {
            $statements[] = new UpsertBuilder(
                grammar : $this->grammar,
                table   : $this->table,
                columns : $this->columns,
                conflict: $this->conflict,
            )->build(rows: $batch);
        }

        return $statements;
    }
}
