<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\CheckSessionValue;

final class ResolveSessionCheckKey
{
    public function handle(string $key) : string
    {
        return $key;
    }
}