<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;

/**
 * CockroachDB Grammar - Distributed PostgreSQL.
 * Inherits from PostgreSQL with distributed tweaks.
 */
final class CockroachDBGrammar extends PostgreSQLGrammar
{
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update) : string
    {
        $sql = $this->compileInsert($queryState);

        $conflictColumns = array_map(
            callback: fn ($col) : string => $this->wrap(value: $col),
            array   : $uniqueBy,
        );
        $conflictClause = implode(separator: ', ', array: $conflictColumns);

        $updates = [];
        foreach ($update as $column) {
            $updates[] = $this->wrap(value: $column) . ' = EXCLUDED.' . $this->wrap(value: $column);
        }

        $updateClause = implode(separator: ', ', array: $updates);

        return sprintf('%s ON CONFLICT (%s) DO UPDATE SET %s', $sql, $conflictClause, $updateClause);
    }

    public function compileImport(string $format, string $path): string
    {
        return sprintf("IMPORT INTO %s '%s'", $format, $path);
    }

    public function compileChangeFeed(string $table, string $sink): string
    {
        return sprintf("CREATE CHANGEFEED FOR %s TO '%s'", $table, $sink);
    }

    public function compileZone(string $zone): string
    {
        return sprintf('ALTER RANGE %s CONFIGURE ZONE', $zone);
    }

    public function compileRegionalInTable(string $region): string
    {
        return 'REGIONAL BY TABLE IN ' . $region;
    }
}
