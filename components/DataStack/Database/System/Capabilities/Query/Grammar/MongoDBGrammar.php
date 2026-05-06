<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
use Override;

/**
 * MongoDB Grammar - Document store with rich query operators.
 */
final class MongoDBGrammar extends BaseGrammar
{
    public function compileSelect(QueryState $queryState): string
    {
        return $this->compileMongoQuery($queryState);
    }

    private function compileMongoQuery(QueryState $queryState): string
    {
        $collection = $this->wrap(value: $queryState->from);
        $filter = $queryState->wheres === [] ? '{}' : $this->compileMongoFilter($queryState);
        $queryState->columns === [] ? '' : $this->compileMongoProjection(columns: $queryState->columns);

        $options = [];
        if ($queryState->limit) {
            $options[] = 'limit: '.$queryState->limit;
        }

        if ($queryState->offset) {
            $options[] = 'skip: '.$queryState->offset;
        }

        if ($queryState->orders !== []) {
            $sort = $this->compileMongoSort(orders: $queryState->orders);
            $options[] = 'sort: '.$sort;
        }

        $optionsStr = $options === [] ? '' : ', {'.implode(separator: ', ', array: $options).'}';

        return sprintf('db.%s.find(%s%s)', $collection, $filter, $optionsStr);
    }

    #[Override]
    public function wrap(mixed $value): string
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        return (string) $value;
    }

    private function compileMongoFilter(QueryState $queryState): string
    {
        if ($queryState->wheres === []) {
            return '{}';
        }

        $conditions = [];
        foreach ($queryState->wheres as $where) {
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
        foreach ($columns as $column) {
            $projection[$column] = 1;
        }

        return ', '.json_encode(value: $projection);
    }

    private function compileMongoSort(array $orders): string
    {
        $sort = [];
        foreach ($orders as $order) {
            $direction = strtoupper(string: (string) $order->direction) === 'DESC' ? -1 : 1;
            $sort[$order->column] = $direction;
        }

        return json_encode(value: $sort);
    }

    #[Override]
    public function compileInsert(QueryState $queryState): string
    {
        $collection = $this->wrap(value: $queryState->from);
        $values = $this->compileMongoDocument(values: $queryState->values);

        return sprintf('db.%s.insertOne(%s)', $collection, $values);
    }

    private function compileMongoDocument(array $values): string
    {
        return json_encode(value: $values);
    }

    #[Override]
    public function compileUpdate(QueryState $queryState): string
    {
        $collection = $this->wrap(value: $queryState->from);
        $filter = $this->compileMongoFilter($queryState);
        $update = $this->compileMongoUpdate(values: $queryState->values);

        return sprintf('db.%s.updateOne(%s, %s)', $collection, $filter, $update);
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
    public function compileDelete(QueryState $queryState): string
    {
        $collection = $this->wrap(value: $queryState->from);
        $filter = $this->compileMongoFilter($queryState);

        return sprintf('db.%s.deleteOne(%s)', $collection, $filter);
    }

    #[Override]
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update): string
    {
        $collection = $this->wrap(value: $queryState->from);
        $filter = $this->compileMongoFilter($queryState);
        $this->compileMongoDocument(values: $queryState->values);
        $setUpdate = $this->compileMongoUpdate(values: $queryState->values);

        return sprintf('db.%s.updateOne(%s, %s, {upsert: true})', $collection, $filter, $setUpdate);
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return sprintf('db.%s.drop()', $this->wrap(value: $table));
    }

    #[Override]
    public function compileDropIfExists(string $table): string
    {
        return sprintf('db.%s.drop()', $this->wrap(value: $table));
    }
}
