<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\DatabaseQueue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use PDO;
use PDOException;

/**
 * Database-backed queue broker.
 *
 * Requires a table created by DatabaseQueueSchema::createTable().
 * Suitable for environments without Redis but needing persistent queues.
 */
final readonly class DatabaseQueue implements QueueBroker
{
    public function __construct(
        private PDO $pdo,
        private string $table = 'avax_queue_jobs',
    ) {}

    /**
     * @param array<string, mixed> $job
     */
    public function push(string $queue, array $job) : void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (queue, payload, attempts, max_attempts, created_at, visible_at)
             VALUES (:queue, :payload, 0, :max_attempts, :now, :now)",
        );

        $stmt->execute([
            'queue' => $queue,
            'payload' => json_encode($job, JSON_THROW_ON_ERROR),
            'max_attempts' => $job['max_attempts'] ?? 3,
            'now' => $this->now(),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pop(string $queue) : ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, payload, attempts, max_attempts FROM {$this->table}
             WHERE queue = :queue AND visible_at <= :now AND reserved_at IS NULL
             ORDER BY id ASC LIMIT 1",
        );

        $stmt->execute(['queue' => $queue, 'now' => $this->now()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        // Mark as reserved
        $reserve = $this->pdo->prepare(
            "UPDATE {$this->table} SET reserved_at = :now WHERE id = :id",
        );
        $reserve->execute(['now' => $this->now(), 'id' => $row['id']]);

        return [
            'id' => (string) $row['id'],
            'job' => json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR),
            'attempts' => (int) $row['attempts'],
            'max_attempts' => (int) $row['max_attempts'],
        ];
    }

    public function size(string $queue) : int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE queue = :queue AND reserved_at IS NULL",
        );
        $stmt->execute(['queue' => $queue]);

        return (int) $stmt->fetchColumn();
    }

    public function remove(string $queue, string $jobId) : void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE id = :id AND queue = :queue",
        );
        $stmt->execute(['id' => $jobId, 'queue' => $queue]);
    }

    public function clear(string $queue) : void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE queue = :queue",
        );
        $stmt->execute(['queue' => $queue]);
    }

    /**
     * Release a reserved job back to the queue.
     */
    public function release(string $jobId, int $delaySeconds = 0) : void
    {
        $visibleAt = time() + $delaySeconds;

        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table}
             SET reserved_at = NULL, attempts = attempts + 1, visible_at = :visible_at
             WHERE id = :id",
        );
        $stmt->execute(['visible_at' => $visibleAt, 'id' => $jobId]);
    }

    /**
     * Delete a job by ID.
     */
    public function deleteById(string $jobId) : void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE id = :id",
        );
        $stmt->execute(['id' => $jobId]);
    }

    /**
     * Move a job to the dead letter table.
     */
    public function deadLetter(string $jobId, string $reason) : void
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                "SELECT queue, payload, attempts FROM {$this->table} WHERE id = :id",
            );
            $stmt->execute(['id' => $jobId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row !== false) {
                $dlStmt = $this->pdo->prepare(
                    "INSERT INTO {$this->table}_dead (queue, payload, attempts, reason, created_at)
                     VALUES (:queue, :payload, :attempts, :reason, :now)",
                );
                $dlStmt->execute([
                    'queue' => $row['queue'],
                    'payload' => $row['payload'],
                    'attempts' => $row['attempts'],
                    'reason' => $reason,
                    'now' => $this->now(),
                ]);
            }

            $this->deleteById($jobId);
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function now() : string
    {
        return date('Y-m-d H:i:s');
    }
}
