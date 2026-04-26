<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Proxies;

use Closure;

final class LazyReference
{
    private mixed $resolved = null;
    private bool  $loaded   = false;

    /**
     * @param callable(): object|null $loader
     */
    public function __construct(private readonly Closure $loader) {}

    public function resolve() : object|null
    {
        if (! $this->loaded) {
            $this->resolved = ($this->loader)();
            $this->loaded   = true;
        }

        return $this->resolved;
    }
}
