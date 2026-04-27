<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\PublicSurface;

use Avax\Components\Database\System\Capabilities\Connections\DatabaseConnection;

final class Database implements DatabaseInterface
{
    private array $connections = [];

    public function connection(string $name = 'default'): DatabaseConnection
    {
        return $this->connections[$name] ??= $this->createConnection($name);
    }

    public function transactions(): bool
    {
        return true;
    }

    public function schema(): SchemaBuilder
    {
        return new SchemaBuilder($this);
    }

    private function createConnection(string $name): DatabaseConnection
    {
        return new DatabaseConnection($name);
    }
}