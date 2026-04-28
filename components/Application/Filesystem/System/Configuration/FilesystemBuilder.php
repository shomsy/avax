<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Configuration;

final class FilesystemBuilder
{
    private array $config = [];

    public function addDisk(string $name, array $config) : self
    {
        $this->config['disks'][$name] = $config;

        return $this;
    }

    public function getConfig() : array
    {
        return $this->config;
    }
}