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

    public function get(string $key, mixed $default = null) : mixed
    {
        $stmt = $this->pdo->prepare("SELECT payload FROM {$this->table} WHERE id = ?");
        $stmt->execute([$key]);
        $payload = $stmt->fetchColumn();

        if ($payload) {
            return unserialize($payload) ?: $default;
        }

        return $default;
    }

    public function put(string $key, mixed $value) : void
    {
        $payload = serialize($value);
        $stmt    = $this->pdo->prepare("REPLACE INTO {$this->table} (id, payload, last_activity) VALUES (?, ?, ?)");
        $stmt->execute([$key, $payload, time()]);
    }

    public function forget(string $key) : void
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$key]);
    }

    public function all() : array
    {
        // Not practically supported in DB store to get ALL sessions data easily
        // Only returning empty array for transaction compatibility
        return [];
    }

    public function flush() : void
    {
        // Truncating is dangerous, skipping for security
    }
}
