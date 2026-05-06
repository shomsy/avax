<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\Grammar;

use components\Database\System\Capabilities\Query\State\QueryState;
use Override;

/**
 * CockroachDB Grammar - Distributed PostgreSQL.
 * Inherits from PostgreSQL with distributed tweaks.
 */
final class CockroachDBGrammar extends PostgreSQLGrammar
{
    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update): string
    {
        $sql = $this->compileInsert(state: $state);

        $conflictColumns = array_map(
            callback: fn ($col) => $this->wrap(value: $col),
            array   : $uniqueBy
        );
        $conflictClause = implode(separator: ', ', array: $conflictColumns);

        $updates = [];
        foreach ($update as $column) {
            $updates[] = $this->wrap(value: $column).' = EXCLUDED.'.$this->wrap(value: $column);
        }

        $updateClause = implode(separator: ', ', array: $updates);

        return "{$sql} ON CONFLICT ({$conflictClause}) DO UPDATE SET {$updateClause}";
    }

    public function compileImport(string $format, string $path): string
    {
        return "IMPORT INTO {$format} '{$path}'";
    }

    public function compileChangeFeed(string $table, string $sink): string
    {
        return "CREATE CHANGEFEED FOR {$table} TO '{$sink}'";
    }

    public function compileZone(string $zone): string
    {
        return "ALTER RANGE {$zone} CONFIGURE ZONE";
    }

    public function compileRegionalInTable(string $region): string
    {
        return "REGIONAL BY TABLE IN {$region}";
    }
}
