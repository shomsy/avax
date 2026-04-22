<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\Execution\Console;

use Avax\Database\System\Capabilities\Migrations\Generate\MigrationGenerator;
use Throwable;

/**
 * Console command to create new migration files.
 */
final readonly class MigrateMakeCommand
{
    private MigrationGenerator $generator;

    public function __construct(
        MigrationGenerator $generator
    )
    {
        $this->generator = $generator;
    }

    public function handle(string $name, string $path, array $options = []) : int
    {
        $table  = $options['table'] ?? null;
        $create = $options['create'] ?? false;

        $this->info(msg: "Creating migration: {$name}");

        try {
            $filepath = $this->generator->generate(name: $name, path: $path, table: $table, create: $create);
            $filename = basename(path: $filepath);
            $this->success();
            echo "  📄 {$filename}\n";

            return 0;
        } catch (Throwable $e) {
            $this->error(msg: 'Failed to create migration: ' . $e->getMessage());

            return 1;
        }
    }

    private function info(string $msg) : void
    {
        echo "\033[36m{$msg}\033[0m\n";
    }

    private function success() : void
    {
        echo "\033[32mMigration created successfully!\033[0m\n";
    }

    private function error(string $msg) : void
    {
        echo "\033[31m{$msg}\033[0m\n";
    }
}
