<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Execution;

use Gemini\Database\Migration\Runner\Migration;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Concrete implementation for executing migration logic.
 */
final readonly class MigrationExecutionService implements MigrationExecutionServiceInterface
{
    public function __construct(private LoggerInterface $logger) {}

    /**
     * @throws \Throwable
     */
    public function runUp(Migration $migration) : void
    {
        try {
            $this->logger->info(message: "Executing migration UP: " . $migration::class);
            $migration->executeUp();
            $this->logger->info(message: "Migration UP completed: " . $migration::class);
        } catch (Throwable $e) {
            $this->logger->error(message: "Migration UP failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @throws \Throwable
     */
    public function runDown(Migration $migration) : void
    {
        try {
            $this->logger->info(message: "Executing migration DOWN: " . $migration::class);
            $migration->executeDown();
            $this->logger->info(message: "Migration DOWN completed: " . $migration::class);
        } catch (Throwable $e) {
            $this->logger->error(message: "Migration DOWN failed: " . $e->getMessage());
            throw $e;
        }
    }
}
