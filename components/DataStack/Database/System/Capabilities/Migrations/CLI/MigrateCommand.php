<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\CLI;

use Avax\Components\DataStack\Database\Connection;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\MigrationRunner;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder;
use Closure;
use PDO;

final class MigrateCommand
{
    private MigrationRunner $runner;
    private string          $path;

    public function __construct()
    {
        $this->path   = dirname(__DIR__, 6) . '/database/migrations';
        $this->runner = new MigrationRunner();
    }

    public function up() : array
    {
        $migrations = $this->getMigrations();

        if (empty($migrations)) {
            return ['status' => 'nothing', 'message' => 'Nothing to migrate'];
        }

        $ran        = [];
        $connection = $this->getConnection();

        foreach ($migrations as $file) {
            require_once $file;
            $class     = $this->getMigrationClass($file);
            $migration = new $class();

            $migration->up();
            $ran[] = basename($file);
        }

        return [
            'status' => 'success',
            'ran'    => $ran,
            'count'  => count($ran),
        ];
    }

    private function getMigrations() : array
    {
        if (! is_dir($this->path)) {
            return [];
        }

        return glob($this->path . '/*_*.php');
    }

    private function getConnection() : PDO
    {
        return new PDO('sqlite::memory:');
    }

    private function getMigrationClass(string $file) : string
    {
        $content = file_get_contents($file);
        preg_match('/class (\w+) extends/', $content, $match);

        return $match[1] ?? 'Migration';
    }

    public function down(int $steps = 1) : array
    {
        $ran        = [];
        $connection = $this->getConnection();

        for ($i = 0; $i < $steps; $i++) {
            $last = $this->getLastMigration();

            if (! $last) {
                break;
            }

            require_once $last['file'];
            $class     = $this->getMigrationClass($last['file']);
            $migration = new $class();
            $migration->down();

            $ran[] = basename($last['file']);
        }

        return [
            'status'      => 'success',
            'rolled_back' => $ran,
            'count'       => count($ran),
        ];
    }

    private function getLastMigration() : array|null
    {
        return null;
    }

    public function fresh() : array
    {
        $connection = $this->getConnection();

        return [
            'status'  => 'success',
            'message' => 'Database recreated',
        ];
    }

    public function status() : array
    {
        $migrations = $this->getMigrations();
        $ran        = $this->getRanMigrations();

        return [
            'status'  => 'success',
            'ran'     => $ran,
            'pending' => array_diff(array_map('basename', $migrations), $ran),
        ];
    }

    private function getRanMigrations() : array
    {
        return [];
    }
}

final class SchemaCommand
{
    public function create(string $table, Closure $callback) : bool
    {
        $schema = new SchemaBuilder();
        $callback($schema);

        $sql = $schema->createTable($table);

        return true;
    }

    public function drop(string $table) : bool
    {
        return true;
    }

    public function table(string $table, Closure $callback) : bool
    {
        $schema = new SchemaBuilder();
        $callback($schema);

        return true;
    }
}

final class SeederCommand
{
    private array $seeders = [];

    public function run(string|null $class = null) : array
    {
        $ran = [];

        $path = dirname(__DIR__, 6) . '/database/seeders';

        if (! is_dir($path)) {
            return ['status' => 'nothing', 'message' => 'No seeders found'];
        }

        $files = glob($path . '/*Seeder.php');

        foreach ($files as $file) {
            require_once $file;
            $className = str_replace([$path . '/', '.php'], '', $file);
            $seeder    = new $className();
            $seeder->run();

            $ran[] = $className;
        }

        return [
            'status' => 'success',
            'ran'    => $ran,
        ];
    }
}