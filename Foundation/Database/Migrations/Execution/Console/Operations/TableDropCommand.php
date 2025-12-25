<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console\Operations;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Throwable;

/**
 * Console command to drop one or more tables.
 */
final class TableDropCommand
{
    public function __construct(
        private readonly QueryBuilder $builder
    ) {}

    /**
     * Handle the command execution.
     *
     * @param string $tables Comma-separated or single table name
     *
     * @return int Exit code
     */
    public function handle(string $tables) : int
    {
        echo "\033[33mCAUTION: You are about to DROP the following table(s): {$tables}\033[0m\n";
        echo "Are you sure? [y/N]: ";

        $confirmation = trim(fgets(STDIN));
        if (strtolower($confirmation) !== 'y') {
            echo "Operation cancelled.\n";

            return 0;
        }

        $tableList = array_map('trim', explode(',', $tables));

        foreach ($tableList as $table) {
            echo "\033[36mDropping table: {$table}...\033[0m ";
            try {
                $this->builder->dropIfExists($table);
                echo "\033[32mDONE\033[0m\n";
            } catch (Throwable $e) {
                echo "\033[31mFAILED: {$e->getMessage()}\033[0m\n";

                return 1;
            }
        }

        return 0;
    }
}
