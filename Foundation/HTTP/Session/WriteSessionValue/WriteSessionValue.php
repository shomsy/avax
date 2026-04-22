<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\WriteSessionValue;

final class WriteSessionValue
{
    public function handle(string $key, mixed $value, int|null $ttl = null) : void
    {
        throw new \RuntimeException('WriteSessionValue not implemented - placeholder for refactor');
    }
}
