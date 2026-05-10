<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs;

/**
 * Schema helper for failed jobs table.
 */
final class FailedJobsSchema
{
    public static function createTableSql(string $table = 'avax_queue_failed_jobs'): string
    {
        return "CREATE TABLE IF NOT EXISTS {$table} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            queue TEXT NOT NULL,
            payload TEXT NOT NULL,
            exception TEXT NOT NULL,
            failed_at TEXT NOT NULL
        )";
    }
}
