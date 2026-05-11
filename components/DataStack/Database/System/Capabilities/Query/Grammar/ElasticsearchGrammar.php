<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Override;

/**
 * Elasticsearch Grammar - Full-text search DSL.
 */
final class ElasticsearchGrammar extends BaseGrammar
{
    public function compileSelect(QueryState $queryState): string
    {
        $index = $this->wrap(value: $queryState->from);
        $query = $this->compileElasticsearchQuery($queryState);

        return sprintf('%s/_search %s', $index, $query);
    }

    public function wrap(mixed $value): string
    {
        return (string) $value;
    }

    private function compileElasticsearchQuery(QueryState $queryState): string
    {
        $query = ['query' => ['match_all' => (object) []]];

        if ($queryState->wheres !== []) {
            $must = [];
            foreach ($queryState->wheres as $where) {
                $must[] = ['match' => [$where->column => $where->value]];
            }

            $query['query'] = ['bool' => ['must' => $must]];
        }

        if ($queryState->limit) {
            $query['size'] = $queryState->limit;
        }

        if ($queryState->orders !== []) {
            $sort = [];
            foreach ($queryState->orders as $order) {
                $sort[$order->column] = ['order' => strtolower(string: (string) $order->direction)];
            }

            $query['sort'] = [$sort];
        }

        return json_encode(value: $query);
    }

    #[Override]
    public function compileInsert(QueryState $queryState): string
    {
        $index = $this->wrap(value: $queryState->from);
        $document = json_encode(value: $queryState->values);

        return sprintf('%s/_doc %s', $index, $document);
    }

    #[Override]
    public function compileUpdate(QueryState $queryState): string
    {
        $index = $this->wrap(value: $queryState->from);
        $id = $queryState->values['id'] ?? '';
        $document = json_encode(value: $queryState->values);

        return sprintf('%s/_doc/%s %s', $index, $id, $document);
    }

    #[Override]
    public function compileDelete(QueryState $queryState): string
    {
        $index = $this->wrap(value: $queryState->from);
        $id = $queryState->values['id'] ?? '';

        return sprintf('%s/_doc/%s', $index, $id);
    }

    #[Override]
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update): string
    {
        $index = $this->wrap(value: $queryState->from);
        $id = $queryState->values['id'] ?? '';
        $document = json_encode(value: $queryState->values);

        return sprintf('%s/_doc/%s %s', $index, $id, $document);
    }

    public function compileMatch(string $field, string $query, float $boost = 1.0): string
    {
        return json_encode(value: ['match' => [$field => ['query' => $query, 'boost' => $boost]]]);
    }

    public function compileMultiMatch(array $fields, string $query): string
    {
        return json_encode(value: ['multi_match' => ['query' => $query, 'fields' => $fields]]);
    }

    public function compileTerm(string $field, mixed $value): string
    {
        return json_encode(value: ['term' => [$field => $value]]);
    }

    public function compileTerms(string $field, array $values): string
    {
        return json_encode(value: ['terms' => [$field => $values]]);
    }

    public function compileRange(string $field, array $range): string
    {
        return json_encode(value: ['range' => [$field => $range]]);
    }

    public function compileBool(array|null $must = null, array|null $mustNot = null, array $should = []) : string
    {
        $must ??= [];
        $mustNot ??= [];
        $bool = [];
        if ($must !== []) {
            $bool['must'] = $must;
        }

        if ($mustNot !== []) {
            $bool['must_not'] = $mustNot;
        }

        if ($should !== []) {
            $bool['should'] = $should;
        }

        return json_encode(value: ['bool' => $bool]);
    }

    public function compileAggregation(string $name, string $type, array $config): string
    {
        return json_encode(value: ['aggs' => [$name => [$type => $config]]]);
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return $table.'/_delete_by_query '.json_encode(value: ['query' => ['match_all' => (object) []]]);
    }
}
