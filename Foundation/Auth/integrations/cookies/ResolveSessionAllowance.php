<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Cookies;

use SensitiveParameter;

/**
 * Decides whether HTTP transport should allow session auth resolution.
 */
final class ResolveSessionAllowance
{
    /**
     * @param array<string, mixed> $cookies
     */
    public function execute(
        array                             $cookies,
        bool                              $allowSession = true,
        #[SensitiveParameter] string|null $sessionCookieName = null
    ) : bool
    {
        if (! $allowSession) {
            return false;
        }

        if ($sessionCookieName === null || $sessionCookieName === '') {
            return true;
        }

        return array_key_exists($sessionCookieName, $cookies);
    }
}
