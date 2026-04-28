<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionFingerprint;

final class ReadSessionFingerprint
{
    private $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function handle() : string|null
    {
        return $this->store->get('_fingerprint');
    }
}