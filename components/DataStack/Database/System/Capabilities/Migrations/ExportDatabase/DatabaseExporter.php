<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\ExportDatabase;

use PDO;
use Throwable;

/**
 * Database exporter.
 *
 * -- intent: provide functionality to export database schema and/or data.
 */
final readonly class DatabaseExporter
{
    public function __construct(private PDO $pdo) {}

    /**
     * Export the database schema and data to a SQL file.
     *
     * -- intent: generate a SQL dump of the database.
     *
     * @param string $path Path to save the export
     * @param string|null $table Optional specific table to export
     *
     * @return string Path to the exported file
     *
     * @throws Throwable If export fails
     */
    public function exportToSql(string $path, ?string $table = null) : string
    {
        $filename = ($table !== null && $table !== '' && $table !== '0' ? $table : 'full_db') . '_export_' . date(format: 'Y_m_d_His') . '.sql';
        $fullPath = rtrim(string: $path, characters: DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (! is_dir(filename: $path)) {
            mkdir(directory: $path, permissions: 0o755, recursive: true);
        }

        $output = "-- Avax Database Export\n";
        $output .= '-- Generated: ' . date(format: 'Y-m-d H:i:s') . "\n";
        $output   .= $table !== null && $table !== '' && $table !== '0' ? "-- Table: {$table}\n\n" : "-- Scope: Full Database\n\n";

        $tables = $table === null ? $this->readTableNames() : [$table];

        foreach ($tables as $table) {
            $quotedTableName = $this->quoteIdentifier(name: $table);

            // 1. Export Schema
            $createTable = $this->readCreateTable(table: $table);
            // noinspection SqlNoDataSourceInspection
            $output .= "DROP TABLE IF EXISTS {$quotedTableName};\n";
            $output      .= $createTable . ';

';

            // 2. Export Data (Simple implementation)
            $rows = $this->readRows(table: $table);
            if ($rows !== []) {
                $output .= sprintf('-- Data for %s%s', $quotedTableName, PHP_EOL);
                foreach ($rows as $row) {
                    $cols = implode(
                        separator: ', ',
                        array    : array_map(callback: fn (string $column) : string => $this->quoteIdentifier(name: $column), array: array_keys(array: $row)),
                    );
                    $vals    = array_map(callback: fn ($v) => is_null(value: $v) ? 'NULL' : $this->pdo->quote(string: (string) $v), array: array_values(array: $row));
                    $valsStr = implode(separator: ', ', array: $vals);
                    // noinspection SqlNoDataSourceInspection
                    $output .= "INSERT INTO {$quotedTableName} ({$cols}) VALUES ({$valsStr});\n";
                }

                $output .= "\n";
            }
        }

        file_put_contents(filename: $fullPath, data: $output);

        return $fullPath;
    }

    /**
     * @return list<string>
     */
    private function readTableNames() : array
    {
        $driver = (string) $this->pdo->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'sqlite' => $this->readFirstColumn(
                sql: "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name",
            ),
            'pgsql'  => $this->readFirstColumn(
                sql: "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename",
            ),
            'sqlsrv' => $this->readFirstColumn(
                sql: "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME",
            ),
            default  => $this->readFirstColumn(sql: 'SHOW TABLES'),
        };
    }

    /**
     * @return list<string>
     */
    private function readFirstColumn(string $sql) : array
    {
        // noinspection SqlNoDataSourceInspection
        $statement = $this->pdo->query(query: $sql);
        $rows      = $statement === false ? [] : $statement->fetchAll(mode: PDO::FETCH_NUM);

        return array_values(array: array_filter(
                                       array   : array_map(callback: static fn (array $row) : string => isset($row[0]) ? (string) $row[0] : '', array: $rows),
                                       callback: static fn (string $value) : bool => $value !== '',
                                   ));
    }

    private function quoteIdentifier(string $name) : string
    {
        $driver = (string) $this->pdo->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'mysql'  => '`' . str_replace(search: '`', replace: '``', subject: $name) . '`',
            'sqlsrv' => '[' . str_replace(search: ']', replace: ']]', subject: $name) . ']',
            default  => '"' . str_replace(search: '"', replace: '""', subject: $name) . '"',
        };
    }

    private function readCreateTable(string $table) : string
    {
        $driver = (string) $this->pdo->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'sqlite' => $this->readSqliteCreateTable(table: $table),
            'mysql'  => $this->readCreateTableFromShow(table: $table),
            default  => '-- Schema export is not implemented for driver ' . $driver,
        };
    }

    private function readSqliteCreateTable(string $table) : string
    {
        $statement = $this->pdo->prepare(
            query: "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = :name LIMIT 1",
        );
        $statement->execute(params: ['name' => $table]);

        $value = $statement->fetchColumn();

        return is_string(value: $value) ? $value : '';
    }

    private function readCreateTableFromShow(string $table) : string
    {
        $quotedTableName = $this->quoteIdentifier(name: $table);

        // noinspection SqlNoDataSourceInspection
        $statement = $this->pdo->query(query: 'SHOW CREATE TABLE ' . $quotedTableName);
        $row       = $statement === false ? false : $statement->fetch(mode: PDO::FETCH_ASSOC);

        if (! is_array(value: $row)) {
            return '';
        }

        foreach (['Create Table', 'Create View'] as $column) {
            if (isset($row[$column]) && is_string(value: $row[$column])) {
                return $row[$column];
            }
        }

        return '';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readRows(string $table) : array
    {
        // noinspection SqlNoDataSourceInspection
        $statement = $this->pdo->query(query: 'SELECT * FROM ' . $this->quoteIdentifier(name: $table));

        return $statement === false ? [] : $statement->fetchAll();
    }
}
