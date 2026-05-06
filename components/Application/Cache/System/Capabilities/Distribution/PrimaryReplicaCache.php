<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution;

use Avax\Components\Application\Cache\System\CacheContract;

final readonly class PrimaryReplicaCache
{
    /**
     * @param  list<CacheContract>  $replicas
     */
    public function __construct(
        private CacheContract $primary,
        private array $replicas,
    ) {
    }

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
