<?php

declare(strict_types=1);

namespace Avax\Database\Integrations\Console;

use Avax\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Throwable;

final readonly class MigrateStatusCommand
{
    public function __construct(private ReadMigrationStatus $readMigrationStatus) {}

    public function handle(string $path) : int
    {
        try {
            $status = $this->readMigrationStatus->read(path: $path);

            if (empty($status['rows'])) {
                echo "No migrations found.\n";

                return 0;
            }

            echo str_pad('Migration', 50) . " | Status  | Integrity\n";
            echo str_repeat('-', 80) . "\n";

            foreach ($status['rows'] as $row) {
                echo str_pad($row['migration'], 50) . ' | '
                    . str_pad($row['status'], 7) . ' | '
                    . $row['integrity'] . "\n";
            }

            echo "\n";
            echo sprintf(
                "Total: %d | Ran: %d | Pending: %d\n",
                $status['summary']['total'],
                $status['summary']['ran'],
                $status['summary']['pending']
            );

            return 0;
        } catch (Throwable $throwable) {
            echo "\033[31mStatus failed:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }
}
