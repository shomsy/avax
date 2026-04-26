<?php

declare(strict_types=1);

namespace components\HTTP\Session\ReadAllSessionValues;

use components\HTTP\Session\SessionStore\SessionStore;

final class ReadAllSessionValues
{
    private SessionStore $store;

    public function __construct(SessionStore $store) { $this->store = $store; }

    public function handle() : array { return $this->store->all(); }
}
