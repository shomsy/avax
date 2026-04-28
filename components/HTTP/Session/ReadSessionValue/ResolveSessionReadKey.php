<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ReadSessionValue;

final class ResolveSessionReadKey
{
    public function handle(string $key) : string
    {
        return $key;
    }
}