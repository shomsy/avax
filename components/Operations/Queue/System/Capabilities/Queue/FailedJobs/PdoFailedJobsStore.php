<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs;

use InvalidArgumentException;
use Override;
use PDO;

/**
 * PDO-based implementation of the failed jobs store.
 */
final readonly class PdoFailedJobsStore implements FailedJobsStore
{
    private string $table;

    public function __construct(
        private PDO $pdo,
        string      $table = 'avax_queue_failed_jobs',
    )
    {
        $this->table = $this->validateTableName($table);
    }

    /**
     * Validate table name to prevent SQL injection via identifier.
     */
    private function validateTableName(string $table) : string
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new InvalidArgumentException(
                "Invalid table name: '{$table}'. Must match /^[A-Za-z_][A-Za-z0-9_]*$/."
            );
        }

        return $table;
    }

    #[Override]
    public function record(string $queue, array $payload, string $reason, string $failedAt) : void
    {
        $this->ensureTable();

        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (queue, payload, exception, failed_at) VALUES (:queue, :payload, :exception, :failed_at)"
        );
        $stmt->execute([
                           ':queue'     => $queue,
                           ':payload'   => json_encode($payload, JSON_THROW_ON_ERROR),
                           ':exception' => $reason,
                           ':failed_at' => $failedAt,
                       ]);
    }

    public function ensureTable() : void
    {
        $this->pdo->exec(FailedJobsSchema::createTableSql($this->table));
    }

    #[Override]
    public function list(string $queue = '') : array
    {
        $this->ensureTable();

        if ($queue !== '') {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE queue = :queue ORDER BY id DESC");
            $stmt->execute([':queue' => $queue]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        }

        if ($stmt === false) {
            return [];
        }

        /** @var list<array{id: int|string, queue: mixed, payload: mixed, exception: mixed, failed_at: mixed}> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static fn (array $row) => [
            'id'        => (string) $row['id'],
            'queue'     => (string) $row['queue'],
            'payload'   => (string) $row['payload'],
            'exception' => (string) $row['exception'],
            'failed_at' => (string) $row['failed_at'],
        ], $rows);
    }

    /**
     * Find a single failed job by ID.
     *
     * @return array{id: string, queue: string, payload: string, exception: string, failed_at: string}|null
     */
    public function find(string $id) : ?array
    {
        $this->ensureTable();

        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id'        => (string) $row['id'],
            'queue'     => $row['queue'],
            'payload'   => $row['payload'],
            'exception' => $row['exception'],
            'failed_at' => $row['failed_at'],
        ];
    }

    /**
     * Remove a failed job by ID.
     */
    public function remove(string $id) : void
    {
        $this->ensureTable();

        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    #[Override]
    public function count(string $queue = '') : int
    {
        $this->ensureTable();

        if ($queue !== '') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE queue = :queue");
            $stmt->execute([':queue' => $queue]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}");
        }

        if ($stmt === false) {
            return 0;
        }

        return (int) $stmt->fetchColumn();
    }

    #[Override]
    public function clear(string $queue = '') : void
    {
        $this->ensureTable();

        if ($queue !== '') {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE queue = :queue");
            $stmt->execute([':queue' => $queue]);
        } else {
            $this->pdo->exec("DELETE FROM {$this->table}");
        }
    }
}
