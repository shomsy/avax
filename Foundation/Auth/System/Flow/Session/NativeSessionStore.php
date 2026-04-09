<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session;

use RuntimeException;

/**
 * Native PHP session store with explicit cookie policy.
 */
final class NativeSessionStore implements SessionStoreInterface
{
    public function __construct(
        private SessionCookieSettings $cookieSettings = new SessionCookieSettings()
    ) {}

    public function regenerate() : string
    {
        $this->start();
        session_regenerate_id(delete_old_session: true);

        return session_id();
    }

    public function start() : void
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException('Session support is disabled.');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
                                      'secure'   => $this->cookieSettings->secure,
                                      'httponly' => $this->cookieSettings->httpOnly,
                                      'samesite' => $this->cookieSettings->sameSite,
                                      'path'     => $this->cookieSettings->path,
                                      'domain'   => $this->cookieSettings->domain,
                                  ]);

        session_start();
    }

    public function id() : string|null
    {
        $this->start();

        return session_id() !== '' ? session_id() : null;
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

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => (bool) $params['secure'],
                'httponly' => (bool) $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
        }

        session_destroy();
    }
}
