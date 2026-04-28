<?php

declare(strict_types=1);

namespace Avax\Components\Database\Integrations\Console;

use Avax\Components\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
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

            echo str_pad(string: 'Migration', length: 50) . " | Status  | Integrity\n";
            echo str_repeat(string: '-', times: 80) . "\n";

            foreach ($status['rows'] as $row) {
                echo str_pad(string: $row['migration'], length: 50) . ' | '
                    . str_pad(string: $row['status'], length: 7) . ' | '
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
