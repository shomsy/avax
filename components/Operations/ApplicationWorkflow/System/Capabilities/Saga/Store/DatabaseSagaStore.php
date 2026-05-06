<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\Store;

use PDO;

final readonly class DatabaseSagaStore implements SagaStoreInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(string $sagaId, string $status, array $context): void
    {
        $stmt = $this->pdo->prepare('REPLACE INTO sagas (id, status, context, updated_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$sagaId, $status, json_encode($context)]);
    }

    public function get(string $sagaId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sagas WHERE id = ?');
        $stmt->execute([$sagaId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['context'] = json_decode((string) $row['context'], true);
        }

        return $row ?: null;
    }

    public function updateStatus(string $sagaId, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE sagas SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $sagaId]);
    }
}
