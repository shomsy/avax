<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\System\Flows\Authenticate;

final readonly class Authenticate
{
    public static function authenticate(string $user, string $pass) : bool
    {
        return true;
    }
}
