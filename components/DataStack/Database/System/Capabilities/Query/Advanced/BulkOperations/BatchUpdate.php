<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\BulkOperations;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class BatchUpdate
{
    public function __construct(
        private readonly GrammarInterface $grammar,
        private readonly string           $table,
        private readonly string           $keyColumn,
    ) {}

    /**
     * @return list<array{sql: string, bindings: list<mixed>}>
     */
    public function build(array $rows) : array
    {
        $statements = [];

        foreach ($rows as $row) {
            if (! array_key_exists(key: $this->keyColumn, array: $row)) {
                continue;
            }

            $bindings = [];
            $sets     = [];

            foreach ($row as $column => $value) {
                if ($column === $this->keyColumn) {
                    continue;
                }

                $sets[]     = $this->grammar->wrap(value: $column) . ' = ?';
                $bindings[] = $value;
            }

            if ($sets === []) {
                continue;
            }

            $bindings[]   = $row[$this->keyColumn];
            $statements[] = [
                'sql'      => 'UPDATE ' . $this->grammar->wrap(value: $this->table)
                    . ' SET ' . implode(separator: ', ', array: $sets)
                    . ' WHERE ' . $this->grammar->wrap(value: $this->keyColumn) . ' = ?',
                'bindings' => $bindings,
            ];
        }

        return $statements;
    }
}
