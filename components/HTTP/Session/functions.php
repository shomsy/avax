<?php

declare(strict_types=1);

/**
 * Session helper functions.
 */

use Avax\Components\Application\Container\System\PublicSurface\Container;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;

if (! function_exists('session')) {
    /**
     * Get the session instance or a value from the session.
     */
    function session(string|null $key = null, mixed $default = null) : mixed
    {
        $container = Container::getInstance();
        $session   = $container->get(SessionInterface::class);

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
    function session_flash(string|null $key = null, mixed $value = null, mixed $default = null) : mixed
    {
        $container = Container::getInstance();
        $session   = $container->get(SessionInterface::class);

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
