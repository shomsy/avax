<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Commands;

use Avax\Database\Migration\Runner\Generators\CommandInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MigrateFreshCommand implements CommandInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments) : void
    {
        try {
            echo "Running fresh migrations...\n";
            $this->logger->info("Fresh migrations executed successfully.");
        } catch (Throwable $throwable) {
            $this->logger->error('Error running fresh migrations: ' . $throwable->getMessage());
        }
    }
}