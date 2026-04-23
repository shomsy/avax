<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\DeleteSessionValue;

final class ResolveSessionDeleteKey
{
    public function handle(string $key) : string
    {
        return $key;
    }
}