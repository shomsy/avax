<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use InvalidArgumentException;

/**
 * CookieManager - Centralized Cookie Policy Enforcement
 *
 * OWASP ASVS 3.4.1 Compliant
 *
 * Enforces secure cookie attributes (Secure, HttpOnly, SameSite).
 * Prevents common cookie-based attacks:
 * - XSS (via HttpOnly)
 * - Man-in-the-middle (via Secure)
 * - CSRF (via SameSite)
 *
 * @package Avax\HTTP\Session\Security
 */
final readonly class CookieManager
{
    /**
     * CookieManager Constructor.
     *
     * @param bool   $secure   Require HTTPS (default: true).
     * @param bool   $httpOnly Prevent JavaScript access (default: true).
     * @param string $sameSite SameSite policy: 'Lax', 'Strict', 'None' (default: 'Lax').
     * @param string $path     Cookie path (default: '/').
     * @param string $domain   Cookie domain (default: '').
     * @param int    $lifetime Cookie lifetime in seconds (default: 0 = session).
     */
    public function __construct(
        private bool   $secure = true,
        private bool   $httpOnly = true,
        private string $sameSite = 'Lax',
        private string $path = '/',
        private string $domain = '',
        private int    $lifetime = 0
    )
    {
        // Validate SameSite
        if (! in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
            throw new InvalidArgumentException(
                "Invalid SameSite value: {$sameSite}. Must be 'Lax', 'Strict', or 'None'."
            );
        }

        // SameSite=None requires Secure flag
        if ($sameSite === 'None' && ! $secure) {
            throw new InvalidArgumentException(
                'SameSite=None requires Secure flag to be true (HTTPS only).'
            );
        }
    }

    /**
     * Create a strict security configuration.
     *
     * - SameSite=Strict
     * - Secure=true
     * - HttpOnly=true
     *
     * @return self
     */
    public static function strict() : self
    {
        return new self(
            secure  : true,
            httpOnly: true,
            sameSite: 'Strict'
        );
    }

    /**
     * Create a lax security configuration (default).
     *
     * - SameSite=Lax
     * - Secure=true
     * - HttpOnly=true
     *
     * @return self
     */
    public static function lax() : self
    {
        return new self(
            secure  : true,
            httpOnly: true,
            sameSite: 'Lax'
        );
    }

    /**
     * Create a development configuration (insecure).
     *
     * - SameSite=Lax
     * - Secure=false
     * - HttpOnly=true
     *
     * @return self
     */
    public static function development() : self
    {
        return new self(
            secure  : false,
            httpOnly: true,
            sameSite: 'Lax'
        );
    }

    /**
     * Set a cookie with enforced security attributes.
     *
     * @param string $name    Cookie name.
     * @param string $value   Cookie value.
     * @param int    $expires Expiration timestamp (0 = session).
     *
     * @return bool True on success.
     */
    public function set(string $name, string $value, int $expires = 0) : bool
    {
        $expires = $expires ?: ($this->lifetime ? time() + $this->lifetime : 0);

        // PHP 7.3+ array format
        return setcookie($name, $value, [
            'expires'  => $expires,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ]);
    }

    /**
     * Delete a cookie.
     *
     * Sets expiration to past time to trigger browser deletion.
     *
     * @param string $name Cookie name.
     *
     * @return bool True on success.
     */
    public function delete(string $name) : bool
    {
        return setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ]);
    }

    /**
     * Get a cookie value.
     *
     * @param string     $name    Cookie name.
     * @param mixed|null $default Default value.
     *
     * @return mixed Cookie value or default.
     */
    public function get(string $name, mixed $default = null) : mixed
    {
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Check if a cookie exists.
     *
     * @param string $name Cookie name.
     *
     * @return bool True if exists.
     */
    public function has(string $name) : bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Configure session cookie parameters.
     *
     * OWASP ASVS 3.2.2 Compliant
     *
     * Applies security policy to PHP session cookies.
     *
     * @return void
     */
    public function configureSessionCookie() : void
    {
        session_set_cookie_params([
            'lifetime' => $this->lifetime,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ]);
    }

    /**
     * Get current cookie configuration.
     *
     * @return array<string, mixed> Configuration array.
     */
    public function getConfig() : array
    {
        return [
            'secure'   => $this->secure,
            'httpOnly' => $this->httpOnly,
            'sameSite' => $this->sameSite,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'lifetime' => $this->lifetime,
        ];
    }

    /**
     * Check if configuration is production-ready.
     *
     * @return bool True if secure configuration.
     */
    public function isSecure() : bool
    {
        return $this->secure && $this->httpOnly && $this->sameSite !== 'None';
    }
}
