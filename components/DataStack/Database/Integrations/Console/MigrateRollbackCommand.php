<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\Integrations\Console;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Throwable;

final readonly class MigrateRollbackCommand
{
    public function __construct(private RollbackMigrations $rollbackMigrations) {}

    public function handle(string $path, int $steps = 1) : int
    {
        try {
            $rolledBack = $this->rollbackMigrations->run(path: $path, steps: $steps);

            if ($rolledBack === []) {
                echo "\033[36mNothing to rollback.\033[0m\n";

                return 0;
            }

            foreach ($rolledBack as $name) {
                echo sprintf('  ✓ %s%s', $name, PHP_EOL);
            }

            echo "\033[32mRolled back " . count(value: $rolledBack) . " migration(s).\033[0m\n";

            return 0;
        } catch (Throwable $throwable) {
            echo sprintf('[31mRollback failed:[0m %s%s', $throwable->getMessage(), PHP_EOL);

            return 1;
        }
    }
}
