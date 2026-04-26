<?php

declare(strict_types=1);

namespace components\HTTP\Session\BindSessionActor;

use components\HTTP\Session\SessionStore\SessionStore;

final class DropBoundSessionActor
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle() : void
    {
        $this->store->delete(key: '_actor');
    }
}