<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session;

use RuntimeException;

/**
 * Native PHP session store with explicit cookie policy.
 */
final class NativeSessionStore implements SessionStoreInterface
{
    private SessionCookieSettings $cookieSettings;

    public function __construct(
        SessionCookieSettings $cookieSettings = new SessionCookieSettings()
    )
    {
        $this->cookieSettings = $cookieSettings;
    }

    public function regenerate() : string
    {
        $this->start();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return session_id();
        }
        if (! session_regenerate_id(delete_old_session: true)) {
            return session_id();
        }

        $sessionId = session_id();

        if ($sessionId === false) {
            throw new RuntimeException(message: 'Session ID is unavailable after regeneration.');
        }

        return $sessionId;
    }

    public function start() : void
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException(message: 'Session support is disabled.');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (headers_sent()) {
            return;
        }

        if (! headers_sent()) {
            session_set_cookie_params([
                                          'secure'   => $this->cookieSettings->secure,
                                          'httponly' => $this->cookieSettings->httpOnly,
                                          'samesite' => $this->cookieSettings->sameSite,
                                          'path'     => $this->cookieSettings->path,
                                          'domain'   => $this->cookieSettings->domain,
                                      ]);
        }

        if (session_status() === PHP_SESSION_DISABLED) {
            return;
        }

        @session_start();
    }

    public function id() : string|null
    {
        $this->start();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        $sessionId = session_id();

        return $sessionId === false || $sessionId === '' ? null : $sessionId;
    }

    public function get(string $key) : mixed
    {
        $this->start();

        return $_SESSION[$key] ?? null;
    }

    public function put(string $key, mixed $value) : void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function forget(string $key) : void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    public function invalidate() : void
    {
        $this->start();

        $_SESSION = [];

        $useCookies = filter_var(ini_get('session.use_cookies'), FILTER_VALIDATE_BOOL);

        if ($useCookies && session_status() === PHP_SESSION_ACTIVE) {
            $params      = session_get_cookie_params();
            $sessionName = session_name();

            if ($sessionName !== false) {
                setcookie($sessionName, '', [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $this->normalizeSameSite(sameSite: $params['samesite']),
                ]);
            }
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * @return 'Lax'|'Strict'|'None'
     */
    private function normalizeSameSite(string $sameSite) : string
    {
        return match (strtolower($sameSite)) {
            'strict' => 'Strict',
            'none'   => 'None',
            default  => 'Lax',
        };
    }
}
