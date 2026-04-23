<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\WriteSessionValue;

final class ResolveSessionWriteKey
{
    public function handle(string $key) : string
    {
        return $key;
    }
}