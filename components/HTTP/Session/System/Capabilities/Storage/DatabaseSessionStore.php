<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

use PDO;

final readonly class DatabaseSessionStore implements SessionStoreInterface
{
    public function __construct(
        private PDO    $pdo,
        private string $table = 'sessions'
    ) {}

    public function read(string $id) : array
    {
        $stmt = $this->pdo->prepare("SELECT payload FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $payload = $stmt->fetchColumn();

        if ($payload) {
            return unserialize($payload) ?: [];
        }

        return [];
    }

    public function write(string $id, array $data) : bool
    {
        $payload = serialize($data);
        $stmt    = $this->pdo->prepare("REPLACE INTO {$this->table} (id, payload, last_activity) VALUES (?, ?, ?)");
        $stmt->execute([$id, $payload, time()]);

        return true;
    }

    public function destroy(string $id) : bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);

        return true;
    }
}
