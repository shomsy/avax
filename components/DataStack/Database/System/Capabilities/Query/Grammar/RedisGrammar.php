<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
use Override;

/**
 * Redis Grammar - Key-Value store commands.
 */
final class RedisGrammar extends BaseGrammar
{
    #[Override]
    public function compileSelect(QueryState $queryState): string
    {
        $key = $this->wrap(value: $queryState->from);

        return 'GET '.$key;
    }

    #[Override]
    public function wrap(mixed $value): string
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        return (string) $value;
    }

    #[Override]
    public function compileInsert(QueryState $queryState): string
    {
        $key = $this->wrap(value: $queryState->from);
        $value = json_encode(value: $queryState->values);

        return sprintf('SET %s %s', $key, $value);
    }

    #[Override]
    public function compileUpdate(QueryState $queryState): string
    {
        $key = $this->wrap(value: $queryState->from);
        $value = json_encode(value: $queryState->values);

        return sprintf('SET %s %s', $key, $value);
    }

    #[Override]
    public function compileDelete(QueryState $queryState): string
    {
        $key = $this->wrap(value: $queryState->from);

        return 'DEL '.$key;
    }

    #[Override]
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update): string
    {
        $key = $this->wrap(value: $queryState->from);
        $value = json_encode(value: $queryState->values);

        return sprintf('SET %s %s', $key, $value);
    }

    public function compileHashSet(string $key, array $fieldValues): string
    {
        $pairs = [];
        foreach ($fieldValues as $field => $value) {
            $pairs[] = sprintf('%s %s', $field, $value);
        }

        return sprintf('HSET %s ', $key).implode(separator: ' ', array: $pairs);
    }

    public function compileHashGet(string $key, ?string $field = null): string
    {
        if ($field === null) {
            return 'HGETALL '.$key;
        }

        return sprintf('HGET %s %s', $key, $field);
    }

    public function compileListPush(string $key, string $value): string
    {
        return sprintf('RPUSH %s %s', $key, $value);
    }

    public function compileListPop(string $key): string
    {
        return 'LPOP '.$key;
    }

    public function compileSetAdd(string $key, string ...$members): string
    {
        return sprintf('SADD %s ', $key).implode(separator: ' ', array: $members);
    }

    public function compileSetMembers(string $key): string
    {
        return 'SMEMBERS '.$key;
    }

    public function compileSortedSet(string $key, array $scores): string
    {
        $pairs = [];
        foreach ($scores as $member => $score) {
            $pairs[] = sprintf('%s %s', $score, $member);
        }

        return sprintf('ZADD %s ', $key).implode(separator: ' ', array: $pairs);
    }

    public function compileSortedSetRange(string $key, int $start, int $stop): string
    {
        return sprintf('ZRANGE %s %d %d', $key, $start, $stop);
    }

    public function compileExpire(string $key, int $seconds): string
    {
        return sprintf('EXPIRE %s %d', $key, $seconds);
    }

    public function compileTtl(string $key): string
    {
        return 'TTL '.$key;
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return 'FLUSHDB';
    }

    #[Override]
    public function compileDropIfExists(string $table): string
    {
        return 'FLUSHDB';
    }
}
