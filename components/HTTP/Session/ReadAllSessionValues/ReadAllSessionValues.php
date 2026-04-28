<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ReadAllSessionValues;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class ReadAllSessionValues
{
    private SessionStore $store;

    public function __construct(SessionStore $store) { $this->store = $store; }

    public function handle() : array { return $this->store->all(); }
}
