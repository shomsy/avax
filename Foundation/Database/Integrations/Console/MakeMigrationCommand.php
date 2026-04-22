<?php

declare(strict_types=1);

namespace Avax\Database\Integrations\Console;

use Avax\Database\System\Capabilities\Migrations\Generate\MigrationGenerator;

final readonly class MakeMigrationCommand
{
    public function __construct(private MigrationGenerator $generator) {}

    /**
     * @param array<string, mixed> $options
     */
    public function handle(string $name, string $path, array $options = []) : int
    {
        return (new \Avax\Database\System\Capabilities\Migrations\Execution\Console\MigrateMakeCommand(
            generator: $this->generator
        ))->handle(name: $name, path: $path, options: $options);
    }
}
