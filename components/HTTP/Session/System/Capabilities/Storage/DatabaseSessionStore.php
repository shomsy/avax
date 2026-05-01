<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

use InvalidArgumentException;
use PDO;

final readonly class DatabaseSessionStore implements SessionStoreInterface
{
    private const ALLOWED_TABLE_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

    public function __construct(
        private PDO $pdo,
        private string $table = 'sessions',
    )
    {
        // Validate table name to prevent SQL injection
        if (! preg_match(self::ALLOWED_TABLE_PATTERN, $this->table)) {
            throw new InvalidArgumentException("Invalid session table name: {$this->table}");
        }
    }

    public function read(string $id) : array
    {
        $stmt = $this->pdo->prepare("SELECT payload FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $payload = $stmt->fetchColumn();

        if ($payload) {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function write(string $id, array $data) : bool
    {
        $payload = json_encode($data, JSON_THROW_ON_ERROR);
        $stmt = $this->pdo->prepare("REPLACE INTO {$this->table} (id, payload, last_activity) VALUES (?, ?, ?)");
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
