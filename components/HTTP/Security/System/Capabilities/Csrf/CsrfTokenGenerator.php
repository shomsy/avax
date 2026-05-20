<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Capabilities\Csrf;

/**
 * CsrfTokenGenerator — thin adapter for single-token CSRF workflows.
 *
 * This class performs NO session I/O.
 * It delegates token generation to CsrfToken (pure value class)
 * and uses timing-safe comparison for validation.
 *
 * Session storage is the responsibility of CsrfTokens,
 * which delegates through the Session PublicSurface authority.
 *
 * For multi-token workflows with expiration and consumption,
 * use CsrfTokens instead.
 */
final class CsrfTokenGenerator
{
    /**
     * Validate a token against the expected token.
     * Returns false for null or empty tokens (fail-closed).
     */
    public function validate(string|null $token, string $expectedToken) : bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        return hash_equals($expectedToken, $token);
    }

    /**
     * Generate a fresh CSRF token.
     * Does NOT write to session.
     */
    public function generate() : string
    {
        return CsrfToken::generate();
    }

    /**
     * Alias for generate().
     * Does NOT write to session.
     */
    public function token() : string
    {
        return $this->generate();
    }

    /**
     * Alias for generate().
     * Does NOT write to session.
     */
    public function regenerate() : string
    {
        return $this->generate();
    }
}
