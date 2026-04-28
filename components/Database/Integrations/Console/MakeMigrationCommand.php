<?php

declare(strict_types=1);

namespace Avax\Components\Database\Integrations\Console;

use Avax\Components\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use Throwable;

final readonly class MakeMigrationCommand
{
    public function __construct(private MigrationGenerator $generator) {}

    /**
     * @param array<string, mixed> $options
     */
    public function handle(string $name, string $path, array $options = []) : int
    {
        try {
            $table  = isset($options['table']) && is_string(value: $options['table']) ? $options['table'] : null;
            $create = (bool) ($options['create'] ?? false);
            $file   = $this->generator->generate(name: $name, path: $path, table: $table, create: $create);

            echo "\033[32mCreated migration:\033[0m {$file}\n";

            return 0;
        } catch (Throwable $throwable) {
            echo "\033[31mFailed to create migration:\033[0m {$throwable->getMessage()}\n";

            return 1;
        }
    }
}
