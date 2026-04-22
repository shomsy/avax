<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Grammar;

use Avax\Database\System\Capabilities\Query\State\QueryState;
use Override;

/**
 * Elasticsearch Grammar - Full-text search DSL.
 */
final class ElasticsearchGrammar extends BaseGrammar
{
    #[Override]
    public function compileSelect(QueryState $state) : string
    {
        $index = $this->wrap(value: $state->from);
        $query = $this->compileElasticsearchQuery(state: $state);

        return "{$index}/_search {$query}";
    }

    #[Override]
    public function wrap(mixed $value) : string
    {
        return (string) $value;
    }

    private function compileElasticsearchQuery(QueryState $state) : string
    {
        $query = ['query' => ['match_all' => (object) []]];

        if (! empty($state->wheres)) {
            $must = [];
            foreach ($state->wheres as $where) {
                $must[] = ['match' => [$where->column => $where->value]];
            }
            $query['query'] = ['bool' => ['must' => $must]];
        }

        if ($state->limit) {
            $query['size'] = $state->limit;
        }

        if (! empty($state->orders)) {
            $sort = [];
            foreach ($state->orders as $order) {
                $sort[$order->column] = ['order' => strtolower($order->direction)];
            }
            $query['sort'] = [$sort];
        }

        return json_encode($query);
    }

    #[Override]
    public function compileInsert(QueryState $state) : string
    {
        $index    = $this->wrap(value: $state->from);
        $document = json_encode($state->values);

        return "{$index}/_doc {$document}";
    }

    #[Override]
    public function compileUpdate(QueryState $state) : string
    {
        $index    = $this->wrap(value: $state->from);
        $id       = $state->values['id'] ?? '';
        $document = json_encode($state->values);

        return "{$index}/_doc/{$id} {$document}";
    }

    #[Override]
    public function compileDelete(QueryState $state) : string
    {
        $index = $this->wrap(value: $state->from);
        $id    = $state->values['id'] ?? '';

        return "{$index}/_doc/{$id}";
    }

    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update) : string
    {
        $index    = $this->wrap(value: $state->from);
        $id       = $state->values['id'] ?? '';
        $document = json_encode($state->values);

        return "{$index}/_doc/{$id} {$document}";
    }

    public function compileMatch(string $field, string $query, float $boost = 1.0) : string
    {
        return json_encode(['match' => [$field => ['query' => $query, 'boost' => $boost]]]);
    }

    public function compileMultiMatch(array $fields, string $query) : string
    {
        return json_encode(['multi_match' => ['query' => $query, 'fields' => $fields]]);
    }

    public function compileTerm(string $field, mixed $value) : string
    {
        return json_encode(['term' => [$field => $value]]);
    }

    public function compileTerms(string $field, array $values) : string
    {
        return json_encode(['terms' => [$field => $values]]);
    }

    public function compileRange(string $field, array $range) : string
    {
        return json_encode(['range' => [$field => $range]]);
    }

    public function compileBool(array $must = [], array $mustNot = [], array $should = []) : string
    {
        $bool = [];
        if (! empty($must)) {
            $bool['must'] = $must;
        }
        if (! empty($mustNot)) {
            $bool['must_not'] = $mustNot;
        }
        if (! empty($should)) {
            $bool['should'] = $should;
        }

        return json_encode(['bool' => $bool]);
    }

    public function compileAggregation(string $name, string $type, array $config) : string
    {
        return json_encode(['aggs' => [$name => [$type => $config]]]);
    }

    #[Override]
    public function compileTruncate(string $table) : string
    {
        return "{$table}/_delete_by_query " . json_encode(['query' => ['match_all' => (object) []]]);
    }
}
