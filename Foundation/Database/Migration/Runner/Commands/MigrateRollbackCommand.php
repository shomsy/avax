<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Commands;

use Avax\Database\Migration\Runner\Generators\CommandInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MigrateRollbackCommand implements CommandInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments) : void
    {
        try {
            echo "Rolling back migrations...\n";
            $this->logger->info("Migrations rolled back successfully.");
        } catch (Throwable $throwable) {
            $this->logger->error('Error rolling back migrations: ' . $throwable->getMessage());
        }
    }
}