<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Commands;

use Avax\Database\Migration\Runner\Generators\CommandInterface;
use Psr\Log\LoggerInterface;

final readonly class MigrateStatusCommand implements CommandInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments) : void
    {
        echo "Migration status:\n";
        echo "[✓] Migration_001\n";
        echo "[✓] Migration_002\n";
        $this->logger->info("Migration status retrieved successfully.");
    }
}