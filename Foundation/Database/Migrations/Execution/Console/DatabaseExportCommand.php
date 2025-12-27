<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console;

use Avax\Migrations\Export\DatabaseExporter;
use Throwable;

/**
 * Console command to export the database.
 */
final class DatabaseExportCommand
{
    public function __construct(
        private readonly DatabaseExporter $exporter
    ) {}

    public function handle(string $path, string|null $table = null) : int
    {
        $target = $table ? "table: {$table}" : "full database";

        echo "\033[33mNOTICE: You are about to export {$target}.\033[0m\n";
        echo "This might take some time depending on your data size. Proceed? [y/N]: ";

        $confirmation = trim(string: fgets(stream: STDIN));
        if (strtolower(string: $confirmation) !== 'y') {
            echo "Operation cancelled.\n";

            return 0;
        }

        echo "\033[36mExporting {$target} to: {$path}...\033[0m\n";

        try {
            $file = $this->exporter->exportToSql(path: $path, table: $table);
            echo "\033[32mExport successful: {$file}\033[0m\n";

            return 0;
        } catch (Throwable $e) {
            echo "\033[31mExport failed: {$e->getMessage()}\033[0m\n";

            return 1;
        }
    }
}
