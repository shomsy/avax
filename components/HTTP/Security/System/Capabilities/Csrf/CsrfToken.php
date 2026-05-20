<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Capabilities\Csrf;

/**
 * CsrfToken — pure value class for CSRF token generation.
 *
 * This class performs NO session I/O.
 * Session storage is the responsibility of CsrfTokens,
 * which delegates through the Session PublicSurface authority.
 */
final readonly class CsrfToken
{
    /**
     * Generate a fresh cryptographically secure CSRF token string.
     *
     * Does NOT write to session. Callers must store the returned
     * token via CsrfTokens or the Session authority.
     */
    public static function generate() : string
    {
        return bin2hex(random_bytes(length: 32));
    }
}
