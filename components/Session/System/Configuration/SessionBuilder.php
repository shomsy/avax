<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Configuration;

final class SessionBuilder
{
    private array $config = [];

    public function setDriver(string $driver): self
    {
        $this->config['driver'] = $driver;

        return $this;
    }

    public function setLifetime(int $lifetime): self
    {
        $this->config['lifetime'] = $lifetime;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}