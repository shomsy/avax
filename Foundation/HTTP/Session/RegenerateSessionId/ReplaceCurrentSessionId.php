<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RegenerateSessionId;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class ReplaceCurrentSessionId
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $newId) : void
    {
        $this->store->put('_session_id', $newId);
    }
}