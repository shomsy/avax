<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseMigration;

use Avax\Components\DataStack\Database\System\Capabilities\MigrationRunner;

final class RunDatabaseMigration
{
    public function __construct(
        private MigrationRunner $runner,
    ) {}

    public function execute() : void
    {
        $this->runner->run();
    }

    public function rollback() : void
    {
        $this->runner->rollback();
    }

    public function reset() : void
    {
        $this->runner->reset();
    }
}
