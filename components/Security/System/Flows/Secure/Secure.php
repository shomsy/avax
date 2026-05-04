<?php

declare(strict_types=1);

namespace Avax\Security\System\Flows\Secure;

final readonly class Secure
{
    public static function protect(mixed $data): mixed
    {
        return $data;
    }
}
