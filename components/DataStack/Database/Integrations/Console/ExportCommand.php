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
            echo sprintf('[32mExported database to:[0m %s%s', $file, PHP_EOL);

            return 0;
        } catch (Throwable $throwable) {
            echo sprintf('[31mExport failed:[0m %s%s', $throwable->getMessage(), PHP_EOL);

            return 1;
        }
    }
}
