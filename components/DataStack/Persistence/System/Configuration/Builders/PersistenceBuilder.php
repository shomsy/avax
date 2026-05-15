<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration\Builders;

final class PersistenceBuilder
{
    /** @var array<string, mixed> */
    private array $config = [];

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
