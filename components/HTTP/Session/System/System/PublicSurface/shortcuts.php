<?php

declare(strict_types=1);

/**
 * Session shortcuts for global access.
 */

use Avax\Components\HTTP\Session\System\System\PublicSurface\SessionInterface;

if (! function_exists('session')) {
    /**
     * Get the session instance or a value from the session.
     *
     *
     * @return mixed|SessionInterface
     */
    function session(?string $key = null, mixed $default = null) : mixed
    {
        $session = app(SessionInterface::class);

        if ($key === null) {
            return $session;
        }

        return $session->get($key, $default);
    }
}

if (! function_exists('session_flash')) {
    /**
     * Get or set a flash message.
     */
    function session_flash(?string $key = null, mixed $value = null, mixed $default = null) : mixed
    {
        $session = app(SessionInterface::class);

        if ($value !== null && $key !== null) {
            $session->flash($key, $value);

            return null;
        }

        if ($key !== null) {
            return $session->getFlash($key, $default);
        }

        return null;
    }
}
