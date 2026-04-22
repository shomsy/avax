<?php

declare(strict_types=1);

namespace Avax\Database\Integrations\Console;

use Avax\Database\System\Capabilities\Migrations\Execution\Repository\MigrationRepository;
use Avax\Database\System\Capabilities\Migrations\Execution\Runner\MigrationRunner;
use Avax\Database\System\Capabilities\Migrations\Generate\MigrationLoader;

final readonly class MigrateCommand
{
    public function __construct(
        private MigrationRepository $repository,
        private MigrationRunner     $runner,
        private MigrationLoader     $loader
    ) {}

    public function handle(string $path, bool $dryRun = false) : int
    {
        return (new \Avax\Database\System\Capabilities\Migrations\Execution\Console\MigrateCommand(
            repository: $this->repository,
            runner    : $this->runner,
            loader    : $this->loader
        ))->handle(path: $path, dryRun: $dryRun);
    }
}
