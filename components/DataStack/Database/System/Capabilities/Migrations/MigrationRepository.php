<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations;

use PDO;

final readonly class MigrationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function ensureTableExists(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        )');
    }

    public function getRan(): array
    {
        return $this->pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function log(string $migration, int $batch): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
        $stmt->execute([$migration, $batch]);
    }

    public function getLastBatchNumber(): int
    {
        return (int) $this->pdo->query('SELECT MAX(batch) FROM migrations')->fetchColumn();
    }
}
