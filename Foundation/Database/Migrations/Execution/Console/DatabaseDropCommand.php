<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Throwable;

/**
 * Console command to drop a database.
 */
final class DatabaseDropCommand
{
    public function __construct(
        private readonly QueryBuilder $builder
    ) {}

    public function handle(string $name) : int
    {
        echo "\033[33mCAUTION: You are about to DROP the entire database: {$name}\033[0m\n";
        echo "Are you sure you want to proceed? [y/N]: ";

        $confirmation = trim(fgets(STDIN));
        if (strtolower($confirmation) !== 'y') {
            echo "Operation cancelled.\n";

            return 0;
        }

        echo "\033[36mDropping database: {$name}...\033[0m\n";

        try {
            $this->builder->dropDatabase(name: $name);
            echo "\033[32mDatabase dropped successfully!\033[0m\n";

            return 0;
        } catch (Throwable $e) {
            echo "\033[31mFailed to drop database: {$e->getMessage()}\033[0m\n";

            return 1;
        }
    }
}
