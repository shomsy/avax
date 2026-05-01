<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Override;

/**
 * YugabyteDB Grammar - Distributed PostgreSQL.
 * Inherits from PostgreSQL with distributed capabilities.
 */
final class YugabyteDBGrammar extends PostgreSQLGrammar
{
    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update): string
    {
        return parent::compileUpsert(state: $state, uniqueBy: $uniqueBy, update: $update);
    }

    public function createTableDistributed(string $table, string $strategy = 'REPLICAS 3'): string
    {
        return "CREATE TABLE {$table} WITH (format_version = 2.0, tablets = {$strategy})";
    }

    public function createIndexDistributed(string $index, string $table, string $columns): string
    {
        return "CREATE INDEX {$index} ON {$table} ({$columns})";

    }

    public function splitAt(string $table, array $keys): string
    {
        $keyList = implode(separator: ', ', array: $keys);

        return "SPLIT AT ({$keyList})";
    }

    public function moveTablet(string $table, string $fromTs, string $toTs): string
    {
        return "ALTER TABLE {$table} MOVE TO TSERVER {$toTs}";
    }

    public function setFollowers(string $table, int $count): string
    {
        return "ALTER TABLE {$table} SET (default_replication_factor = {$count})";
    }
}
