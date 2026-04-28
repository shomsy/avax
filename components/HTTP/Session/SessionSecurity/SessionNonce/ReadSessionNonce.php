<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionNonce;

final class ReadSessionNonce
{
    private $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function handle() : string|null
    {
        return $this->store->get('_nonce');
    }
}