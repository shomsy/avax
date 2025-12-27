<?php

declare(strict_types=1);

namespace Avax\Migrations\Export;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Throwable;

/**
 * Database exporter.
 *
 * -- intent: provide functionality to export database schema and/or data.
 */
final class DatabaseExporter
{
    public function __construct(
        private readonly QueryBuilder $builder
    ) {}

    /**
     * Export the database schema and data to a SQL file.
     *
     * -- intent: generate a SQL dump of the database.
     *
     * @param string      $path  Path to save the export
     * @param string|null $table Optional specific table to export
     *
     * @return string Path to the exported file
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
        $output .= "-- Generated: " . date(format: 'Y-m-d H:i:s') . "\n";
        $output .= $table ? "-- Table: {$table}\n\n" : "-- Scope: Full Database\n\n";

        $tables = $table ? [['name' => $table]] : $this->builder->raw(value: "SHOW TABLES")->get();

        foreach ($tables as $tableRow) {
            $tableName = array_values(array: $tableRow)[0];

            // 1. Export Schema
            $createTable = $this->builder->raw(value: "SHOW CREATE TABLE `{$tableName}`")->get()[0];
            $output      .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $output      .= "{$createTable['Create Table']};\n\n";

            // 2. Export Data (Simple implementation)
            $rows = $this->builder->from(table: $tableName)->get();
            if (! empty($rows)) {
                $output .= "-- Data for `{$tableName}`\n";
                foreach ($rows as $row) {
                    $cols    = implode(separator: '`, `', array: array_keys(array: $row));
                    $vals    = array_map(callback: fn ($v) => is_null(value: $v) ? 'NULL' : "'" . addslashes(string: (string) $v) . "'", array: array_values(array: $row));
                    $valsStr = implode(separator: ', ', array: $vals);
                    $output  .= "INSERT INTO `{$tableName}` (`{$cols}`) VALUES ({$valsStr});\n";
                }
                $output .= "\n";
            }
        }

        file_put_contents(filename: $fullPath, data: $output);

        return $fullPath;
    }
}
