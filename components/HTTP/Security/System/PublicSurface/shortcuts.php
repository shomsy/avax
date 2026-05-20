<?php

declare(strict_types=1);

/**
 * HTTP Security shortcuts for global access.
 *
 * All CSRF token operations delegate through the Security PublicSurface
 * which owns CsrfTokens — the single CSRF token authority.
 *
 * These functions are convenience wrappers only.
 * No session I/O happens here.
 */
if (! function_exists('csrf_token')) {
    /**
     * Get or generate a CSRF token.
     *
     * Delegates to Security PublicSurface → CsrfTokens.
     * CsrfTokens is the single authority for token generation,
     * expiration, and consumption.
     */
    function csrf_token() : string
    {
        $security = app(\Avax\Components\HTTP\Security\System\PublicSurface\Security::class);
        assert($security instanceof \Avax\Components\HTTP\Security\System\PublicSurface\Security);

        return $security->csrfToken();
    }
}

if (! function_exists('csrf_field')) {
    /**
     * Generate a CSRF token hidden form field.
     */
    function csrf_field() : string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (! function_exists('csrf_method')) {
    /**
     * Generate a CSRF token meta tag.
     */
    function csrf_method() : string
    {
        return '<meta name="csrf-token" content="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (! function_exists('secure_headers')) {
    /**
     * Set security headers on the response.
     *
     * @param array<string, string> $headers
     */
    function secure_headers(array $headers = []) : void
    {
        $defaults = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-XSS-Protection'       => '1; mode=block',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
        ];

        $combined = array_merge($defaults, $headers);

        foreach ($combined as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }
    }
}
