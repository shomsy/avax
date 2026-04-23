<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\TerminateSession;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class DropStoredSessionValues
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle() : void
    {
        $this->store->flush();
    }
}