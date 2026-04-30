<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Capabilities\Csrf;

final readonly class CsrfVerifier
{
    public static function verify(string $token, string|null $sessionToken = null) : bool
    {
        return hash_equals(
            known_string: $sessionToken ?? $_SESSION['_token'] ?? '',
            user_string : $token,
        );
    }
}
