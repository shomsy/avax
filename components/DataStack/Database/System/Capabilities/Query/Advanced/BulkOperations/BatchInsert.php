<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\BulkOperations;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class BatchInsert
{
    public function __construct(
        private GrammarInterface $grammar,
        private string $table,
        private array $columns,
        private int $batchSize = 100,
    ) {
    }

    /**
     * @return list<array{sql: string, bindings: list<mixed>}>
     */
    public function build(array $rows): array
    {
        $statements = [];

        foreach (array_chunk(array: $rows, size: $this->batchSize) as $batch) {
            $bindings = [];
            $groups = [];

            foreach ($batch as $row) {
                $groups[] = '('.implode(separator: ', ', array: array_fill(start_index: 0, count: count(value: $this->columns), value: '?')).')';

                foreach ($this->columns as $column) {
                    $bindings[] = $row[$column] ?? null;
                }
            }

            $statements[] = [
                'sql' => 'INSERT INTO '.$this->grammar->wrap(value: $this->table)
                    .' ('.$this->wrappedColumns().') VALUES '.implode(separator: ', ', array: $groups),
                'bindings' => $bindings,
            ];
        }

        return $statements;
    }

    private function wrappedColumns(): string
    {
        return implode(
            separator: ', ',
            array    : array_map(callback: fn ($column): string => $this->grammar->wrap(value: $column), array: $this->columns),
        );
    }
}
