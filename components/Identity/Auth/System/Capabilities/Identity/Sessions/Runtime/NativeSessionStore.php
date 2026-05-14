<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime;

use RuntimeException;

/**
 * Native PHP session store with explicit cookie policy.
 */
final class NativeSessionStore implements SessionStoreInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $cliSession = [];

    private string $cliSessionId = '';

    private bool $cliFallbackActive = false;

    public function __construct(private readonly SessionCookieSettings $sessionCookieSettings) {}

    public function regenerate() : string
    {
        $this->start();

        if ($this->cliFallbackActive) {
            $this->cliSessionId = $this->generateCliSessionId();

            return $this->cliSessionId;
        }

        if (! $this->nativeSessionActive()) {
            throw new RuntimeException(message: 'Session regeneration requires an active native session.');
        }

        if (! session_regenerate_id(delete_old_session: true)) {
            throw new RuntimeException(message: 'Session regeneration failed.');
        }

        return $this->readNativeSessionId();
    }

    public function start() : void
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException(message: 'Session support is disabled.');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (! $this->canStartAfterOutput() && headers_sent()) {
            return;
        }

        if ($this->canStartAfterOutput() && headers_sent()) {
            $this->activateCliFallback();

            return;
        }

        if (! headers_sent()) {
            session_set_cookie_params(lifetime_or_options: [
                                                               'secure'   => $this->sessionCookieSettings->secure,
                                                               'httponly' => $this->sessionCookieSettings->httpOnly,
                                                               'samesite' => $this->normalizeSameSite(sameSite: $this->sessionCookieSettings->sameSite),
                                                               'path'     => $this->sessionCookieSettings->path,
                                                               'domain'   => $this->sessionCookieSettings->domain,
                                                           ]);
        }

        session_start();

        if (! $this->nativeSessionActive() && $this->canStartAfterOutput()) {
            $this->activateCliFallback();
        }
    }

    private function canStartAfterOutput() : bool
    {
        return in_array(needle: PHP_SAPI, haystack: ['cli', 'phpdbg'], strict: true);
    }

    private function activateCliFallback() : void
    {
        $this->cliFallbackActive = true;
    }

    /**
     * @return 'Lax'|'Strict'|'None'
     */
    private function normalizeSameSite(string $sameSite) : string
    {
        return match (strtolower(string: $sameSite)) {
            'strict' => 'Strict',
            'none'   => 'None',
            default  => 'Lax',
        };
    }

    private function nativeSessionActive() : bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    private function generateCliSessionId() : string
    {
        return 'cli-session-' . str_replace(search: '.', replace: '', subject: uniqid(prefix: '', more_entropy: true));
    }

    private function readNativeSessionId() : string
    {
        $sessionId = session_id();

        if ($sessionId === false || $sessionId === '') {
            throw new RuntimeException(message: 'Session ID is unavailable.');
        }

        return $sessionId;
    }

    public function id() : ?string
    {
        $this->start();

        if ($this->cliFallbackActive) {
            return $this->cliSessionId !== '' ? $this->cliSessionId : null;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        $sessionId = session_id();

        return $sessionId === false || $sessionId === '' ? null : $sessionId;
    }

    public function get(string $key) : mixed
    {
        $this->start();

        if ($this->cliFallbackActive) {
            return $this->cliSession[$key] ?? null;
        }

        return $_SESSION[$key] ?? null;
    }

    public function put(string $key, mixed $value) : void
    {
        $this->start();

        if ($this->cliFallbackActive) {
            $this->cliSession[$key] = $value;

            return;
        }

        $_SESSION[$key] = $value;
    }

    public function forget(string $key) : void
    {
        $this->start();

        if ($this->cliFallbackActive) {
            unset($this->cliSession[$key]);

            return;
        }

        unset($_SESSION[$key]);
    }

    public function invalidate() : void
    {
        $this->start();

        if ($this->cliFallbackActive) {
            $this->cliSession   = [];
            $this->cliSessionId = '';

            return;
        }

        $_SESSION = [];

        $useCookies = ini_get(option: 'session.use_cookies') !== '0';

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
}
