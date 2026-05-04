<?php

declare(strict_types=1);

namespace Avax\Benchmarks\Performance\System\Capabilities\LazyLoading;

use Closure;

final class LazyValue
{
    private bool $resolved = false;

    private mixed $value = null;

    public function __construct(private readonly Closure $resolver)
    {
    }

    public function get(): mixed
    {
        if (!$this->resolved) {
            $this->value = ($this->resolver)();
            $this->resolved = true;
        }

        return $this->value;
    }

    public function resolved(): bool
    {
        return $this->resolved;
    }
}
