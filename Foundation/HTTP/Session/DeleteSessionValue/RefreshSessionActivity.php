<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\DeleteSessionValue;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class RefreshSessionActivity
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle() : void
    {
        $this->store->put(key: '_last_activity', value: time());
    }
}