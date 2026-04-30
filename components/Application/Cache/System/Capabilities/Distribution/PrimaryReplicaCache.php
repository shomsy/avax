<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution;

final readonly class PrimaryReplicaCache
{
    public function __construct(
        private mixed $primary,
        private array $replicas,
    ) {}

    public function get(string $key): mixed
    {
        $replica = $this->replicas[array_rand($this->replicas)];

        return $replica->get($key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->primary->set($key, $value);
    }
}
