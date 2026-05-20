<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Capabilities\Csrf;

/**
 * CsrfVerifier — timing-safe CSRF token comparison.
 *
 * This class performs NO session I/O.
 * The caller must supply both the submitted token and the
 * expected session-stored token.
 *
 * Session token retrieval is the responsibility of CsrfTokens,
 * which delegates through the Session PublicSurface authority.
 */
final readonly class CsrfVerifier
{
    /**
     * Verify a submitted token against the expected session token.
     *
     * @param string|null $token        The token submitted by the client.
     * @param string|null $sessionToken The expected token from session storage.
     *
     * Returns false if either value is null (fail-closed).
     */
    public static function verify(string|null $token, string|null $sessionToken) : bool
    {
        if ($token === null || $sessionToken === null) {
            return false;
        }

        return hash_equals(known_string: $sessionToken, user_string: $token);
    }
}
