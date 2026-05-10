<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\DatabaseQueue;

use PDO;

/**
 * Creates the database table required by DatabaseQueue.
 */
final class DatabaseQueueSchema
{
    /**
     * Create the queue jobs table.
     */
    public static function createTable(PDO $pdo, string $table = 'avax_queue_jobs') : void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS {$table} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                queue VARCHAR(255) NOT NULL DEFAULT 'default',
                payload TEXT NOT NULL,
                attempts INTEGER NOT NULL DEFAULT 0,
                max_attempts INTEGER NOT NULL DEFAULT 3,
                reserved_at DATETIME DEFAULT NULL,
                visible_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL
            )",
        );

        $pdo->exec(
            "CREATE INDEX IF NOT EXISTS idx_{$table}_queue_visible
             ON {$table} (queue, visible_at, reserved_at)",
        );
    }

    /**
     * Create the dead letter table.
     */
    public static function createDeadLetterTable(PDO $pdo, string $table = 'avax_queue_jobs_dead') : void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS {$table} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                queue VARCHAR(255) NOT NULL DEFAULT 'default',
                payload TEXT NOT NULL,
                attempts INTEGER NOT NULL DEFAULT 0,
                reason TEXT,
                created_at DATETIME NOT NULL
            )",
        );
    }

    /**
     * Drop both tables.
     */
    public static function dropTables(PDO $pdo, string $table = 'avax_queue_jobs') : void
    {
        $pdo->exec("DROP TABLE IF EXISTS {$table}_dead");
        $pdo->exec("DROP TABLE IF EXISTS {$table}");
    }
}
