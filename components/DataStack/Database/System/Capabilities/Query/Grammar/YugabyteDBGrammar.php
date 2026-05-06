<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;

/**
 * YugabyteDB Grammar - Distributed PostgreSQL.
 * Inherits from PostgreSQL with distributed capabilities.
 */
final class YugabyteDBGrammar extends PostgreSQLGrammar
{
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update): string
    {
        return parent::compileUpsert($queryState, $uniqueBy, $update);
    }

    public function createTableDistributed(string $table, string $strategy = 'REPLICAS 3'): string
    {
        return sprintf('CREATE TABLE %s WITH (format_version = 2.0, tablets = %s)', $table, $strategy);
    }

    public function createIndexDistributed(string $index, string $table, string $columns): string
    {
        return sprintf('CREATE INDEX %s ON %s (%s)', $index, $table, $columns);

    }

    public function splitAt(string $table, array $keys): string
    {
        $keyList = implode(separator: ', ', array: $keys);

        return sprintf('SPLIT AT (%s)', $keyList);
    }

    public function moveTablet(string $table, string $fromTs, string $toTs): string
    {
        return sprintf('ALTER TABLE %s MOVE TO TSERVER %s', $table, $toTs);
    }

    public function setFollowers(string $table, int $count): string
    {
        return sprintf('ALTER TABLE %s SET (default_replication_factor = %d)', $table, $count);
    }
}
