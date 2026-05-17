<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\BulkOperations;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;
use Avax\Database\System\Capabilities\Query\ValueObjects\Expression;

final class BulkInserter
{
    private int $batchSize = 100;

    public function __construct(
        private GrammarInterface $grammar,
        private string $table,
        private array $columns
    ) {
    }

    public function batchSize(int $size): self
    {
        $this->batchSize = $size;

        return $this;
    }

    public function execute(array $rows): array
    {
        $results = [];
        $batches = array_chunk(array: $rows, size: $this->batchSize);

        foreach ($batches as $batch) {
            $results[] = $this->executeBatch(batch: $batch);
        }

        return $results;
    }

    private function executeBatch(array $batch): bool
    {
        $columns = implode(separator: ', ', array: array_map(
            callback: fn ($col) => $this->grammar->wrap(value: $col),
            array   : $this->columns
        ));

        $valueGroups = [];
        foreach ($batch as $row) {
            $placeholders = [];
            foreach ($row as $value) {
                $placeholders[] = $value instanceof Expression ? $value->getValue() : '?';
            }
            $valueGroups[] = '('.implode(separator: ', ', array: $placeholders).')';
        }

        $sql = 'INSERT INTO '.$this->grammar->wrap(value: $this->table).' ('.$columns.') VALUES '.implode(separator: ', ', array: $valueGroups);

        return true;
    }

    public function getBindings(): array
    {
        return [];
    }
}
