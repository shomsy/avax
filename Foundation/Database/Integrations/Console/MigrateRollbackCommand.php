<?php

declare(strict_types=1);

namespace Avax\Database\Integrations\Console;

use Avax\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Throwable;

final readonly class MigrateRollbackCommand
{
    public function __construct(private RollbackMigrations $rollbackMigrations) {}

    public function handle(string $path, int $steps = 1) : int
    {
        try {
            $rolledBack = $this->rollbackMigrations->run(path: $path, steps: $steps);

            if (empty($rolledBack)) {
                echo "\033[36mNothing to rollback.\033[0m\n";

                return 0;
            }

            foreach ($rolledBack as $name) {
                echo "  ✓ {$name}\n";
            }

            echo "\033[32mRolled back " . count($rolledBack) . " migration(s).\033[0m\n";

            return 0;
        } catch (Throwable $throwable) {
            echo "\033[31mRollback failed:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }
}
