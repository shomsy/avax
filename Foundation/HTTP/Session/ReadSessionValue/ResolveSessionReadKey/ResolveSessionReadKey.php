<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\ReadSessionValue;

final class ResolveSessionReadKey
{
    public function handle(string $key) : string
    {
        return $key;
    }
}