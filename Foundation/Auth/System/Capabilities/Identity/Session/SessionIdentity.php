<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Session;

use SensitiveParameter;

/**
 * Standard implementation of session-based identity storage within the Auth System.
 */
final class SessionIdentity implements SessionIdentityInterface
{
    private string $sessionKey = 'auth_user_id';

    public function __construct(#[SensitiveParameter] string|null $sessionKey = null)
    {
        if ($sessionKey !== null) {
            $this->sessionKey = $sessionKey;
        }
    }

    public function issue(int $userId) : void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(delete_old_session: true);
        $_SESSION[$this->sessionKey] = $userId;
    }

    public function getUserId() : int|null
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $_SESSION[$this->sessionKey] ?? null;
    }

    public function clear() : void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION[$this->sessionKey]);
        session_destroy();
    }

    public function check() : bool
    {
        return $this->getUserId() !== null;
    }
}
