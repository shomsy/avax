<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Configuration;

final class DatabaseBuilder
{
    private array $config = [];

    public function addConnection(string $name, array $config): self
    {
        $this->config['connections'][$name] = $config;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}