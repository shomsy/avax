<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\CLI;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder;
use PDO;

final readonly class MigrateCommand
{
    private string $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__, 6).'/database/migrations';
    }

    public function up(): array
    {
        $migrations = $this->getMigrations();

        if ($migrations === []) {
            return ['status' => 'nothing', 'message' => 'Nothing to migrate'];
        }

        $ran = [];
        $pdo = $this->getConnection();

        foreach ($migrations as $file) {
            require_once $file;
            $class = $this->getMigrationClass($file);
            $migration = new $class();

            $migration->up();
            $ran[] = basename((string) $file);
        }

        return [
            'status' => 'success',
            'ran' => $ran,
            'count' => count($ran),
        ];
    }

    private function getMigrations(): array
    {
        if (! is_dir($this->path)) {
            return [];
        }

        return glob($this->path.'/*_*.php');
    }

    private function getConnection(): PDO
    {
        return new PDO('sqlite::memory:');
    }

    private function getMigrationClass(string $file): string
    {
        $content = file_get_contents($file);
        preg_match('/class (\w+) extends/', $content, $match);

        return $match[1] ?? 'Migration';
    }

    public function down(int $steps = 1): array
    {
        $ran = [];
        $pdo = $this->getConnection();

        for ($i = 0; $i < $steps; $i++) {
            $last = $this->getLastMigration();

            if ($last === null || $last === []) {
                break;
            }

            require_once $last['file'];
            $class = $this->getMigrationClass($last['file']);
            $migration = new $class();
            $migration->down();

            $ran[] = basename((string) $last['file']);
        }

        return [
            'status' => 'success',
            'rolled_back' => $ran,
            'count' => count($ran),
        ];
    }

    private function getLastMigration(): ?array
    {
        return null;
    }

    public function fresh(): array
    {
        $this->getConnection();

        return [
            'status' => 'success',
            'message' => 'Database recreated',
        ];
    }

    public function status(): array
    {
        $migrations = $this->getMigrations();
        $ran = $this->getRanMigrations();

        return [
            'status' => 'success',
            'ran' => $ran,
            'pending' => array_diff(array_map(basename(...), $migrations), $ran),
        ];
    }

    private function getRanMigrations(): array
    {
        return [];
    }
}
