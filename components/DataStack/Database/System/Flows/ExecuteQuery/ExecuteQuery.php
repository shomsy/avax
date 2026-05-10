<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\ExecuteQuery;

use PDO;
use PDOStatement;

/**
 * Executes a raw SQL query with optional bound parameters.
 *
 * @param list<mixed>|array<string, mixed> $params
 */
final readonly class ExecuteQuery
{
    /**
     * @param list<mixed>|array<string, mixed> $params
     * @phpstan-return list<array<string, mixed>>|int
     * @return array<string, mixed>|int  Rows for SELECT, affected count for write queries
     */
    public function execute(PDO $connection, string $sql, array $params = []) : array|int
    {
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);

        if ($this->isReadQuery($sql)) {
            /** @var list<array<string, mixed>> $rows */
            $rows = $stmt->fetchAll();

            return $rows;
        }

        return $stmt->rowCount();
    }

    /**
     * @param list<mixed>|array<string, mixed> $params
     */
    public function executeCursor(PDO $connection, string $sql, array $params = []) : PDOStatement
    {
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    private function isReadQuery(string $sql) : bool
    {
        $trimmed = ltrim($sql);

        return str_starts_with(strtoupper($trimmed), 'SELECT')
            || str_starts_with(strtoupper($trimmed), 'SHOW')
            || str_starts_with(strtoupper($trimmed), 'DESCRIBE')
            || str_starts_with(strtoupper($trimmed), 'EXPLAIN');
    }
}
