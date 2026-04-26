<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionSecurity\SessionNonce;

use Random\RandomException;

final class RefreshSessionNonce
{
    private $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    /**
     * @throws RandomException
     */
    public function handle() : string
    {
        $nonce = bin2hex(random_bytes(16));
        $this->store->put('_nonce', $nonce);

        return $nonce;
    }
}