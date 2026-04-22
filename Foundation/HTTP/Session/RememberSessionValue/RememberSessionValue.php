<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RememberSessionValue;

final class RememberSessionValue
{
    public function handle(string $key, callable $callback, int|null $ttl = null) : mixed
    {
        throw new \RuntimeException('RememberSessionValue not implemented - placeholder for refactor');
    }
}
