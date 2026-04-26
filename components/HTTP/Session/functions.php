<?php

declare(strict_types=1);

use Avax\HTTP\Session\SessionInterface;

if (! function_exists(function: 'session')) {
    /**
     * Get or set session values, or retrieve the Session instance.
     *
     * @param string|null $key
     * @param mixed       $value
     *
     * @return mixed
     */
    function session(string|null $key = null, mixed $value = null) : mixed
    {
        $session = app(abstract: SessionInterface::class);

        if ($key === null) {
            return $session;
        }

        if ($value === null) {
            return $session->get(key: $key);
        }

        $session->set(key: $key, value: $value);

        return null;
    }
}
