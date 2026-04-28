<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration;

final class PersistenceBuilder
{
    private array $config = [];

    public function getConfig() : array
    {
        return $this->config;
    }
}