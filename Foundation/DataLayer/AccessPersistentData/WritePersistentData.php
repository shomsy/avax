<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use InvalidArgumentException;
use Throwable;

final readonly class WritePersistentData
{
    public function __construct(private ExecuteRawDataQuery $executeRawDataQuery) {}

    public function write(PersistentDataRequest $request) : PersistentDataResult
    {
        if ($request->isSelect()) {
            return PersistentDataResult::failure(
                $request->operation,
                new PersistentDataFailure(
                    sprintf('WritePersistentData::write() does not support SELECT operations. Use ReadPersistentData instead.')
                )
            );
        }

        return $this->executeRawDataQuery->execute($request);
    }

    public function insert(string $table, array $values) : PersistentDataResult
    {
        return $this->write(PersistentDataRequest::insert($table, $values));
    }

    public function insertGetId(string $table, array $values, string $idColumn = 'id') : ?string
    {
        $result = $this->insert($table, $values);

        if (! $result->success) {
            throw new PersistentDataFailure(
                sprintf('Insert failed: %s', $result->failure?->getMessage() ?? 'Unknown error')
            );
        }

        return $result->lastInsertId;
    }

    public function update(string $table, array $values, string $where, array $bindings = []) : PersistentDataResult
    {
        return $this->write(PersistentDataRequest::update($table, $values, $where, $bindings));
    }

    public function updateById(string $table, array $values, mixed $id, string $idColumn = 'id') : PersistentDataResult
    {
        return $this->update($table, $values, "{$idColumn} = :id", ['id' => $id]);
    }

    public function delete(string $table, string $where, array $bindings = []) : PersistentDataResult
    {
        return $this->write(PersistentDataRequest::delete($table, $where, $bindings));
    }

    public function deleteById(string $table, mixed $id, string $idColumn = 'id') : PersistentDataResult
    {
        return $this->delete($table, "{$idColumn} = :id", ['id' => $id]);
    }

    public function upsert(
        string $table,
        array  $values,
        array  $uniqueColumns,
        array  $updateColumns = []
    ) : PersistentDataResult
    {
        if (empty($uniqueColumns)) {
            throw new InvalidArgumentException('Upsert requires at least one unique column.');
        }

        $columns      = array_keys($values);
        $placeholders = array_map(fn ($i) => ":{$i}", array_keys($values));

        $onDuplicate = [];
        if (empty($updateColumns)) {
            foreach ($columns as $col) {
                if (! in_array($col, $uniqueColumns, true)) {
                    $onDuplicate[] = "{$col} = VALUES({$col})";
                }
            }
        } else {
            foreach ($updateColumns as $col) {
                $onDuplicate[] = "{$col} = VALUES({$col})";
            }
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders),
            implode(', ', $onDuplicate)
        );

        return $this->write(PersistentDataRequest::raw($sql, $values));
    }

    public function bulkInsert(string $table, array $rows) : PersistentDataResult
    {
        if (empty($rows)) {
            return PersistentDataResult::empty(PersistentDataOperation::INSERT);
        }

        $firstRow = reset($rows);
        $columns  = array_keys($firstRow);

        $values_sql  = [];
        $allBindings = [];

        foreach ($rows as $index => $row) {
            $placeholders = [];
            foreach (array_keys($row) as $key) {
                $param               = "{$key}_{$index}";
                $placeholders[]      = ":{$param}";
                $allBindings[$param] = $row[$key];
            }
            $values_sql[] = sprintf('(%s)', implode(', ', $placeholders));
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $table,
            implode(', ', $columns),
            implode(', ', $values_sql)
        );

        return $this->write(PersistentDataRequest::raw($sql, $allBindings));
    }

    public function bulkDelete(string $table, array $ids, string $idColumn = 'id') : PersistentDataResult
    {
        if (empty($ids)) {
            return PersistentDataResult::empty(PersistentDataOperation::DELETE);
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $sql          = sprintf('DELETE FROM %s WHERE %s IN (%s)', $table, $idColumn, $placeholders);

        return $this->write(PersistentDataRequest::raw($sql, array_values($ids)));
    }

    public function increment(string $table, string $column, mixed $amount = 1, string $where = '1=1', array $bindings = []) : PersistentDataResult
    {
        $sql = sprintf('UPDATE %s SET %s = %s + :amount WHERE %s', $table, $column, $column, $where);

        $allBindings = array_merge(['amount' => $amount], $bindings);

        return $this->write(PersistentDataRequest::raw($sql, $allBindings));
    }

    public function decrement(string $table, string $column, mixed $amount = 1, string $where = '1=1', array $bindings = []) : PersistentDataResult
    {
        return $this->increment($table, $column, -$amount, $where, $bindings);
    }

    public function execute(string $sql, array $bindings = []) : PersistentDataResult
    {
        return $this->write(PersistentDataRequest::raw($sql, $bindings));
    }
}