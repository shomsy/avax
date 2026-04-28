<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\DeleteSessionValue;

final class ResolveSessionDeleteKey
{
    public function handle(string $key) : string
    {
        return $key;
    }
}