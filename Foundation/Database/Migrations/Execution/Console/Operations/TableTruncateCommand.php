<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Console\Operations;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Throwable;

/**
 * Console command to truncate one or more tables.
 */
final readonly class TableTruncateCommand
{
    public function __construct(
        private QueryBuilder $builder
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
        echo "\033[33mCAUTION: You are about to TRUNCATE (clear) the following table(s): {$tables}\033[0m\n";
        echo "Are you sure? [y/N]: ";

        $confirmation = trim(string: fgets(stream: STDIN));
        if (strtolower(string: $confirmation) !== 'y') {
            echo "Operation cancelled.\n";

            return 0;
        }

        $tableList = array_map(callback: 'trim', array: explode(separator: ',', string: $tables));

        foreach ($tableList as $table) {
            echo "\033[36mTruncating table: {$table}...\033[0m ";
            try {
                $this->builder->truncate(table: $table);
                echo "\033[32mDONE\033[0m\n";
            } catch (Throwable $e) {
                echo "\033[31mFAILED: {$e->getMessage()}\033[0m\n";

                return 1;
            }
        }

        return 0;
    }
}
