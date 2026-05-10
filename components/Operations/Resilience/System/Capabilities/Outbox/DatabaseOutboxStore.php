<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Outbox;

use PDO;
use PDOException;

/**
 * PDO-backed outbox store for persistent message delivery.
 *
 * Requires a table created by DatabaseOutboxSchema::createTable().
 */
final readonly class DatabaseOutboxStore implements OutboxStore
{
    public function __construct(
        private PDO $pdo,
        private string $table = 'avax_outbox',
    ) {}

    public function enqueue(string $event, array $payload) : void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (event_type, payload, processed, created_at)
             VALUES (:event, :payload, 0, :now)",
        );

        $stmt->execute([
            'event' => $event,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'now' => date('Y-m-d H:i:s'),
        ]);
    }

    public function dequeue(int $limit = 10) : array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, event_type, payload, created_at FROM {$this->table}
             WHERE processed = 0 ORDER BY id ASC LIMIT :limit",
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (string) $row['id'],
                'event' => $row['event_type'],
                'payload' => json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR),
                'created_at' => $row['created_at'],
            ];
        }

        return $results;
    }

    public function markProcessed(string $id) : void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET processed = 1, processed_at = :now WHERE id = :id",
        );
        $stmt->execute(['now' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    public function pendingCount() : int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE processed = 0",
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
