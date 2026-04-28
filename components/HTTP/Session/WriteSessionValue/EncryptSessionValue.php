<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\WriteSessionValue;

final class EncryptSessionValue
{
    public function handle(mixed $value) : mixed
    {
        return $value;
    }
}