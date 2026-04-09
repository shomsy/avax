<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Session;

use RuntimeException;
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
        $this->ensureSessionAvailable();

        session_regenerate_id(delete_old_session: true);
        $_SESSION[$this->sessionKey] = $userId;
    }

    public function getUserId() : int|null
    {
        $this->ensureSessionAvailable();

        $storedUserId = $_SESSION[$this->sessionKey] ?? null;

        if (is_int($storedUserId)) {
            return $storedUserId;
        }

        if (is_string($storedUserId) && ctype_digit($storedUserId)) {
            return (int) $storedUserId;
        }

        return null;
    }

    public function clear() : void
    {
        $this->ensureSessionAvailable();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $options = [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => (bool) $params['secure'],
                'httponly' => (bool) $params['httponly'],
            ];

            if (isset($params['samesite']) && $params['samesite'] !== '') {
                $options['samesite'] = $params['samesite'];
            }

            setcookie(session_name(), '', $options);
        }

        session_destroy();
    }

    public function check() : bool
    {
        return $this->getUserId() !== null;
    }

    private function ensureSessionAvailable() : void
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException(message: 'Session support is disabled.');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
