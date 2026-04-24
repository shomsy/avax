<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\TerminateSession;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class TerminateSession
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $reason = 'logout') : void
    {
        $this->store->flush();
    }
}
