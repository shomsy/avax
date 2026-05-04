<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Storage;

/**
 * Native PHP session store implementation using $_SESSION superglobal.
 */
final class NativeSessionStore implements SessionStoreInterface
{
    /**
     * @return array<string, mixed>
     */
    public function read(string $id) : array
    {
        $this->ensureStarted();

        return $_SESSION ?? [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data) : bool
    {
        $this->ensureStarted();

        $_SESSION = $data;

        return true;
    }

    public function destroy(string $id) : bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    (string) session_name(),
                    '',
                    ['expires' => time() - 42000, 'path' => $params['path'], 'domain' => $params['domain'], 'secure' => $params['secure'], 'httponly' => $params['httponly']],
                );
            }

            return session_destroy();
        }

        return false;
    }

    private function ensureStarted() : void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
