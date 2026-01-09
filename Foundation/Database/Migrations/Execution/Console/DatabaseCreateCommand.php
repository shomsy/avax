<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Throwable;

/**
 * Console command to create a new database.
 */
final readonly class DatabaseCreateCommand
{
    public function __construct(
        private QueryBuilder $builder
    ) {}

    public function handle(string $name) : int
    {
        echo "\033[36mCreating database: {$name}...\033[0m\n";

        try {
            $this->builder->createDatabase(name: $name);
            echo "\033[32mDatabase created successfully!\033[0m\n";

            return 0;
        } catch (Throwable $e) {
            echo "\033[31mFailed to create database: {$e->getMessage()}\033[0m\n";

            return 1;
        }
    }
}
