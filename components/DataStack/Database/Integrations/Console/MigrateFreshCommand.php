<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\Integrations\Console;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use PDO;
use Throwable;

final readonly class MigrateFreshCommand
{
    public function __construct(
        private Connections    $connections,
        private MigrateCommand $migrateCommand
    ) {}

    public function handle(string $path, bool $dryRun = false) : int
    {
        try {
            if ($dryRun) {
                echo "\033[36mDRY RUN MODE:\033[0m Database would be dropped and re-migrated.\n";
            } else {
                $this->dropAllTables();
            }

            return $this->migrateCommand->handle(path: $path, dryRun: $dryRun);
        } catch (Throwable $throwable) {
            echo "\033[31mFresh migration failed:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }

    /**
     * @throws Throwable
     */
    private function dropAllTables() : void
    {
        $pdo    = $this->connections->pdo();
        $driver = (string) $pdo->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);
        $tables = $this->readTableNames(pdo: $pdo, driver: $driver);

        if ($tables === []) {
            return;
        }

        match ($driver) {
            'sqlite' => $pdo->exec(statement: 'PRAGMA foreign_keys = OFF'),
            'mysql'  => $pdo->exec(statement: 'SET FOREIGN_KEY_CHECKS=0'),
            default  => null,
        };

        foreach ($tables as $table) {
            // noinspection SqlNoDataSourceInspection
            $pdo->exec(statement: $this->dropTableStatement(table: $table, driver: $driver));
        }

        match ($driver) {
            'sqlite' => $pdo->exec(statement: 'PRAGMA foreign_keys = ON'),
            'mysql'  => $pdo->exec(statement: 'SET FOREIGN_KEY_CHECKS=1'),
            default  => null,
        };
    }

    /**
     * @return list<string>
     */
    private function readTableNames(PDO $pdo, string $driver) : array
    {
        $sql = match ($driver) {
            'sqlite' => "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name",
            'pgsql'  => "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename",
            'sqlsrv' => "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME",
            default  => 'SHOW TABLES',
        };

        // noinspection SqlNoDataSourceInspection
        $statement = $pdo->query(query: $sql);
        $rows      = $statement === false ? [] : $statement->fetchAll(mode: PDO::FETCH_NUM);

        return array_values(array: array_filter(
                                       array   : array_map(callback: static fn (array $row) => isset($row[0]) ? (string) $row[0] : '', array: $rows),
                                       callback: static fn (string $value) => $value !== ''
                                   ));
    }

    private function dropTableStatement(string $table, string $driver) : string
    {
        $quoted = $this->quoteIdentifier(name: $table, driver: $driver);

        return match ($driver) {
            'pgsql' => "DROP TABLE IF EXISTS {$quoted} CASCADE",
            default => "DROP TABLE IF EXISTS {$quoted}",
        };
    }

    private function quoteIdentifier(string $name, string $driver) : string
    {
        return match ($driver) {
            'mysql'  => '`' . str_replace(search: '`', replace: '``', subject: $name) . '`',
            'sqlsrv' => '[' . str_replace(search: ']', replace: ']]', subject: $name) . ']',
            default  => '"' . str_replace(search: '"', replace: '""', subject: $name) . '"',
        };
    }
}
