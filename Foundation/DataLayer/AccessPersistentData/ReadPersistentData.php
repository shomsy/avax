<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

final readonly class ReadPersistentData
{
    public function __construct(private ExecuteRawDataQuery $executeRawDataQuery) {}

    public function read(PersistentDataRequest $request) : PersistentDataResult
    {
        if (! $request->isSelect()) {
            return PersistentDataResult::failure(
                $request->operation,
                new PersistentDataFailure(
                    sprintf('ReadPersistentData::read() only supports SELECT operations. Got: %s', $request->operation->value)
                )
            );
        }

        return $this->executeRawDataQuery->execute($request);
    }

    public function findById(string $table, mixed $id, string $idColumn = 'id') : PersistentDataResult
    {
        $request = PersistentDataRequest::select(
            sprintf('SELECT * FROM %s WHERE %s = :id', $table, $idColumn),
            ['id' => $id]
        );

        return $this->read($request);
    }

    public function findOne(string $table, array $conditions) : PersistentDataResult
    {
        [$sql, $bindings] = $this->buildWhereClause($table, $conditions, 1);
        $request = PersistentDataRequest::select($sql, $bindings);

        return $this->read($request);
    }

    public function findAll(string $table, array $conditions = []) : PersistentDataResult
    {
        [$sql, $bindings] = $this->buildWhereClause($table, $conditions);

        if (! empty($conditions)) {
            $sql .= ' WHERE ' . $conditions['_where'] ?? '';
            unset($conditions['_where'], $conditions['_order_by'], $conditions['_limit'], $conditions['_offset']);
        }

        $orderBy = $conditions['_order_by'] ?? null;
        $limit   = $conditions['_limit'] ?? null;
        $offset  = $conditions['_offset'] ?? null;

        if ($orderBy) {
            $sql .= sprintf(' ORDER BY %s', $orderBy);
        }
        if ($limit) {
            $sql .= sprintf(' LIMIT %d', $limit);
        }
        if ($offset) {
            $sql .= sprintf(' OFFSET %d', $offset);
        }

        $request = PersistentDataRequest::select($sql, $bindings);

        return $this->read($request);
    }

    public function count(string $table, array $conditions = []) : int
    {
        $whereClause = empty($conditions) ? '' : ' WHERE ' . ($conditions['_where'] ?? '');

        $sql     = sprintf('SELECT COUNT(*) as cnt FROM %s%s', $table, $whereClause);
        $request = PersistentDataRequest::select($sql, $bindings ?? []);

        $result = $this->read($request);

        return (int) ($result->first()['cnt'] ?? 0);
    }

    public function exists(string $table, array $conditions) : bool
    {
        return $this->count($table, $conditions) > 0;
    }

    public function existsById(string $table, mixed $id, string $idColumn = 'id') : bool
    {
        return $this->exists($table, [$idColumn => $id]);
    }

    public function paginate(
        string $table,
        array  $conditions = [],
        int    $page = 1,
        int    $perPage = 20
    ) : array
    {
        $page    = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        $conditions['_limit']  = $perPage;
        $conditions['_offset'] = $offset;

        $items = $this->findAll($table, $conditions);
        $total = $this->count($table, $conditions);

        return [
            'items'       => $items->rows,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    private function buildWhereClause(string $table, array $conditions, ?int $limit = null) : array
    {
        if (empty($conditions)) {
            $limiter = $limit ? sprintf(' LIMIT %d', $limit) : '';

            return ["SELECT * FROM {$table}{$limiter}", []];
        }

        $whereParts = [];
        $bindings   = [];

        foreach ($conditions as $key => $value) {
            if (in_array($key, ['_where', '_order_by', '_limit', '_offset', '_group_by'], true)) {
                continue;
            }

            if ($value === null) {
                $whereParts[] = "{$key} IS NULL";
            } elseif (is_array($value)) {
                $placeholders = implode(', ', array_fill(0, count($value), '?'));
                $whereParts[] = "{$key} IN ({$placeholders})";
                $bindings     = array_merge($bindings, array_values($value));
            } else {
                $whereParts[]   = "{$key} = :{$key}";
                $bindings[$key] = $value;
            }
        }

        $whereClause = implode(' AND ', $whereParts);
        $limiter     = $limit ? sprintf(' LIMIT %d', $limit) : '';

        return ["SELECT * FROM {$table} WHERE {$whereClause}{$limiter}", $bindings];
    }
}