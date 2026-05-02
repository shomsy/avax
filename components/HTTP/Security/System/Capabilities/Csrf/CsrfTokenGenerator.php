<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Capabilities\Csrf;

/**
 * CsrfTokenGenerator - generates and stores CSRF tokens in session.
 * Provides a simpler interface than CsrfTokens for basic use cases.
 */
final class CsrfTokenGenerator
{
    private const string SESSION_KEY = '_csrf_token';

    public function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        return hash_equals($this->token(), $token);
    }

    public function token(): string
    {
        return $_SESSION[self::SESSION_KEY] ?? $this->generate();
    }

    public function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->storeToken($token);

        return $token;
    }

    private function storeToken(string $token): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[self::SESSION_KEY] = $token;
        }
    }

    public function regenerate(): string
    {
        return $this->generate();
    }
}
