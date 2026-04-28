<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\Integrations\Console;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use Throwable;

final readonly class ExportCommand
{
    public function __construct(private DatabaseExporter $databaseExporter) {}

    public function handle(string $path, string|null $table = null) : int
    {
        try {
            $file = $this->databaseExporter->exportToSql(path: $path, table: $table);
            echo "\033[32mExported database to:\033[0m {$file}\n";

            return 0;
        } catch (Throwable $throwable) {
            echo "\033[31mExport failed:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }
}
