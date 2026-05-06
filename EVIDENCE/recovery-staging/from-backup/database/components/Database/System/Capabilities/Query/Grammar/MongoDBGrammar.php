<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Grammar;

use Avax\Database\System\Capabilities\Query\State\QueryState;
use Avax\Database\System\Capabilities\Query\ValueObjects\Expression;
use Override;

/**
 * MongoDB Grammar - Document store with rich query operators.
 */
final class MongoDBGrammar extends BaseGrammar
{
    #[Override]
    public function compileSelect(QueryState $state): string
    {
        return $this->compileMongoQuery(state: $state);
    }

    private function compileMongoQuery(QueryState $state): string
    {
        $collection = $this->wrap(value: $state->from);
        $filter = empty($state->wheres) ? '{}' : $this->compileMongoFilter(state: $state);
        $projection = empty($state->columns) ? '' : $this->compileMongoProjection(columns: $state->columns);

        $options = [];
        if ($state->limit) {
            $options[] = "limit: {$state->limit}";
        }
        if ($state->offset) {
            $options[] = "skip: {$state->offset}";
        }
        if (! empty($state->orders)) {
            $sort = $this->compileMongoSort(orders: $state->orders);
            $options[] = "sort: {$sort}";
        }

        $optionsStr = empty($options) ? '' : ', {'.implode(separator: ', ', array: $options).'}';

        return "db.{$collection}.find({$filter}{$optionsStr})";
    }

    #[Override]
    public function wrap(mixed $value): string
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        return (string) $value;
    }

    private function compileMongoFilter(QueryState $state): string
    {
        if (empty($state->wheres)) {
            return '{}';
        }

        $conditions = [];
        foreach ($state->wheres as $where) {
            $column = $where->column;
            $operator = $where->operator;
            $value = $where->value;

            $mongoOp = match ($operator) {
                '=' => $value,
                '!=' => ['$ne' => $value],
                '>' => ['$gt' => $value],
                '>=' => ['$gte' => $value],
                '<' => ['$lt' => $value],
                '<=' => ['$lte' => $value],
                'LIKE' => ['$regex' => str_replace(search: '%', replace: '.*', subject: $value)],
                'IN' => ['$in' => $value],
                'NOT IN' => ['$nin' => $value],
                'IS NULL' => ['$exists' => false],
                'IS NOT NULL' => ['$exists' => true, '$ne' => null],
                default => $value,
            };

            $conditions[$column] = $mongoOp;
        }

        return json_encode(value: $conditions);
    }

    private function compileMongoProjection(array $columns): string
    {
        $projection = [];
        foreach ($columns as $col) {
            $projection[$col] = 1;
        }

        return ', '.json_encode(value: $projection);
    }

    private function compileMongoSort(array $orders): string
    {
        $sort = [];
        foreach ($orders as $order) {
            $direction = strtoupper(string: $order->direction) === 'DESC' ? -1 : 1;
            $sort[$order->column] = $direction;
        }

        return json_encode(value: $sort);
    }

    #[Override]
    public function compileInsert(QueryState $state): string
    {
        $collection = $this->wrap(value: $state->from);
        $values = $this->compileMongoDocument(values: $state->values);

        return "db.{$collection}.insertOne({$values})";
    }

    private function compileMongoDocument(array $values): string
    {
        return json_encode(value: $values);
    }

    #[Override]
    public function compileUpdate(QueryState $state): string
    {
        $collection = $this->wrap(value: $state->from);
        $filter = $this->compileMongoFilter(state: $state);
        $update = $this->compileMongoUpdate(values: $state->values);

        return "db.{$collection}.updateOne({$filter}, {$update})";
    }

    private function compileMongoUpdate(array $values): string
    {
        $set = [];
        foreach ($values as $key => $value) {
            $set[$key] = $value;
        }

        return json_encode(value: ['$set' => $set]);
    }

    #[Override]
    public function compileDelete(QueryState $state): string
    {
        $collection = $this->wrap(value: $state->from);
        $filter = $this->compileMongoFilter(state: $state);

        return "db.{$collection}.deleteOne({$filter})";
    }

    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update): string
    {
        $collection = $this->wrap(value: $state->from);
        $filter = $this->compileMongoFilter(state: $state);
        $document = $this->compileMongoDocument(values: $state->values);
        $setUpdate = $this->compileMongoUpdate(values: $state->values);

        return "db.{$collection}.updateOne({$filter}, {$setUpdate}, {upsert: true})";
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return "db.{$this->wrap(value: $table)}.drop()";
    }

    #[Override]
    public function compileDropIfExists(string $table): string
    {
        return "db.{$this->wrap(value: $table)}.drop()";
    }
}
