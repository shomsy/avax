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
    public function exportToSql(string $path, ?string $table = null) : string
    {
        $filename = ($table ?: 'full_db') . '_export_' . date('Y_m_d_His') . '.sql';
        $fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $output = "-- Avax Database Export\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= $table ? "-- Table: {$table}\n\n" : "-- Scope: Full Database\n\n";

        $tables = $table ? [['name' => $table]] : $this->builder->raw("SHOW TABLES")->get();

        foreach ($tables as $tableRow) {
            $tableName = array_values($tableRow)[0];

            // 1. Export Schema
            $createTable = $this->builder->raw("SHOW CREATE TABLE `{$tableName}`")->get()[0];
            $output      .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $output      .= "{$createTable['Create Table']};\n\n";

            // 2. Export Data (Simple implementation)
            $rows = $this->builder->from($tableName)->get();
            if (! empty($rows)) {
                $output .= "-- Data for `{$tableName}`\n";
                foreach ($rows as $row) {
                    $cols    = implode('`, `', array_keys($row));
                    $vals    = array_map(fn ($v) => is_null($v) ? 'NULL' : "'" . addslashes((string) $v) . "'", array_values($row));
                    $valsStr = implode(', ', $vals);
                    $output  .= "INSERT INTO `{$tableName}` (`{$cols}`) VALUES ({$valsStr});\n";
                }
                $output .= "\n";
            }
        }

        file_put_contents($fullPath, $output);

        return $fullPath;
    }
}
