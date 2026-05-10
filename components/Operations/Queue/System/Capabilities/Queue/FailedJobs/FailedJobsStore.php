<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs;

use PDO;

/**
 * FailedJobsStore — manages failed queue jobs for CLI operations.
 */
final readonly class FailedJobsStore
{
    public function __construct(
        private PDO $pdo,
        private string $table = 'avax_queue_failed_jobs',
    ) {}

    /**
     * Record a failed job.
     *
     * @param array<string, mixed> $payload
     */
    public function record(string $queue, array $payload, string $exception, string $failedAt): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (queue, payload, exception, failed_at)
             VALUES (:queue, :payload, :exception, :failed_at)",
        );

        $stmt->execute([
            'queue' => $queue,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'exception' => $exception,
            'failed_at' => $failedAt,
        ]);
    }

    /**
     * List all failed jobs.
     *
     * @return list<array{id: string, queue: string, payload: string, exception: string, failed_at: string}>
     */
    public function list(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, queue, payload, exception, failed_at FROM {$this->table} ORDER BY id DESC",
        );
        $stmt->execute();

        /** @var list<array{id: string, queue: string, payload: string, exception: string, failed_at: string}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            return [
                'id' => (string) $row['id'],
                'queue' => (string) $row['queue'],
                'payload' => (string) $row['payload'],
                'exception' => (string) $row['exception'],
                'failed_at' => (string) $row['failed_at'],
            ];
        }, $rows);
    }

    /**
     * Get a single failed job by ID.
     *
     * @return array{id: string, queue: string, payload: string, exception: string, failed_at: string}|null
     */
    public function find(string $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, queue, payload, exception, failed_at FROM {$this->table} WHERE id = :id",
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (string) $row['id'],
            'queue' => $row['queue'],
            'payload' => $row['payload'],
            'exception' => $row['exception'],
            'failed_at' => $row['failed_at'],
        ];
    }

    /**
     * Remove a failed job (used after successful retry).
     */
    public function remove(string $id): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE id = :id",
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * Clear all failed jobs.
     */
    public function clear(): void
    {
        $this->pdo->exec("DELETE FROM {$this->table}");
    }

    /**
     * Count failed jobs.
     */
    public function count(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
