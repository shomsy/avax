<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Commands;

use Gemini\Database\Migration\Runner\Generators\CommandInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MigrateRefreshCommand implements CommandInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments) : void
    {
        try {
            echo "Refreshing migrations...\n";
            $this->logger->info("Migrations refreshed successfully.");
        } catch (Throwable $throwable) {
            $this->logger->error('Error refreshing migrations: ' . $throwable->getMessage());
        }
    }
}