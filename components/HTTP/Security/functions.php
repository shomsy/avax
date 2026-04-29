<?php

declare(strict_types=1);

/**
 * HTTP Security helper functions.
 */

if (! function_exists('csrf_token')) {
    /**
     * Get or generate a CSRF token.
     */
    function csrf_token() : string
    {
        if (isset($_SESSION['_csrf_token'])) {
            return $_SESSION['_csrf_token'];
        }

        $token                   = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;

        return $token;
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
            header("{$name}: {$value}");
        }
    }
}
