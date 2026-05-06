<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseMigration;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;

final readonly class RunDatabaseMigration
{
    public function __construct(
        private MigrationRunner $migrationRunner,
    ) {
    }

    public function execute(): void
    {
        $this->migrationRunner->run();
    }

    public function rollback(): void
    {
        $this->migrationRunner->rollback();
    }

    public function reset(): void
    {
        $this->migrationRunner->reset();
    }
}
