<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Configuration;

final class DataBuilder
{
    private array $config = [];

    public function getConfig(): array
    {
        return $this->config;
    }
}