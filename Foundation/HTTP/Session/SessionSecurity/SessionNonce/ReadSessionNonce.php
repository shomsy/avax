<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionNonce;

final class ReadSessionNonce
{
    private $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function handle() : ?string
    {
        return $this->store->get('_nonce');
    }
}