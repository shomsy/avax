<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\ExportDatabase;

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
     * @param string      $path  Path to save the export
     * @param string|null $table Optional specific table to export
     *
     * @return string Path to the exported file
     *
     * @throws Throwable If export fails
     */
    public function exportToSql(string $path, string|null $table = null) : string
    {
        $filename = ($table ?: 'full_db') . '_export_' . date(format: 'Y_m_d_His') . '.sql';
        $fullPath = rtrim(string: $path, characters: DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (! is_dir(filename: $path)) {
            mkdir(directory: $path, permissions: 0755, recursive: true);
        }

        $output = "-- Avax Database Export\n";
        $output .= '-- Generated: ' . date(format: 'Y-m-d H:i:s') . "\n";
        $output .= $table ? "-- Table: {$table}\n\n" : "-- Scope: Full Database\n\n";

        $tables = $table === null ? $this->readTableNames() : [$table];

        foreach ($tables as $tableName) {
            $quotedTableName = $this->quoteIdentifier(name: $tableName);

            // 1. Export Schema
            $createTable = $this->readCreateTable(table: $tableName);
            // noinspection SqlNoDataSourceInspection
            $output .= "DROP TABLE IF EXISTS {$quotedTableName};\n";
            $output .= "{$createTable};\n\n";

            // 2. Export Data (Simple implementation)
            $rows = $this->readRows(table: $tableName);
            if (! empty($rows)) {
                $output .= "-- Data for {$quotedTableName}\n";
                foreach ($rows as $row) {
                    $cols = implode(
                        separator: ', ',
                        array    : array_map(fn (string $column) => $this->quoteIdentifier(name: $column), array_keys(array: $row))
                    );
                    $vals    = array_map(callback: static fn ($v) => is_null(value: $v) ? 'NULL' : "'" . addslashes(string: (string) $v) . "'", array: array_values(array: $row));
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
                sql: "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            ),
            'pgsql'  => $this->readFirstColumn(
                sql: "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
            ),
            'sqlsrv' => $this->readFirstColumn(
                sql: "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
            ),
            default  => $this->readFirstColumn(sql: 'SHOW TABLES'),
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

    /**
     * @return list<array<string, mixed>>
     */
    private function readRows(string $table) : array
    {
        // noinspection SqlNoDataSourceInspection
        $statement = $this->pdo->query(query: 'SELECT * FROM ' . $this->quoteIdentifier(name: $table));

        return $statement === false ? [] : $statement->fetchAll();
    }

    /**
     * @return list<string>
     */
    private function readFirstColumn(string $sql) : array
    {
        // noinspection SqlNoDataSourceInspection
        $statement = $this->pdo->query(query: $sql);
        $rows      = $statement === false ? [] : $statement->fetchAll(PDO::FETCH_NUM);

        return array_values(array_filter(
                                array_map(static fn (array $row) => isset($row[0]) ? (string) $row[0] : '', $rows),
                                static fn (string $value) => $value !== ''
                            ));
    }

    private function readCreateTableFromShow(string $table) : string
    {
        $quotedTableName = $this->quoteIdentifier(name: $table);

        // noinspection SqlNoDataSourceInspection
        $statement = $this->pdo->query(query: "SHOW CREATE TABLE {$quotedTableName}");
        $row       = $statement === false ? false : $statement->fetch(PDO::FETCH_ASSOC);

        if (! is_array($row)) {
            return '';
        }

        foreach (['Create Table', 'Create View'] as $column) {
            if (isset($row[$column]) && is_string($row[$column])) {
                return $row[$column];
            }
        }

        return '';
    }

    private function readSqliteCreateTable(string $table) : string
    {
        $statement = $this->pdo->prepare(
            query: "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = :name LIMIT 1"
        );
        $statement->execute(params: ['name' => $table]);

        $value = $statement->fetchColumn();

        return is_string($value) ? $value : '';
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
}
