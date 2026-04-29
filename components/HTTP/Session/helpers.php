<?php
declare(strict_types=1);

use Avax\Components\HTTP\Session\System\PublicSurface\Session;

if (!function_exists('session')) {
    /**
     * Get the session instance or a specific value.
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        $session = app(Session::class);

        if ($key === null) {
            return $session;
        }

        return $session->get($key, $default);
    }
}
