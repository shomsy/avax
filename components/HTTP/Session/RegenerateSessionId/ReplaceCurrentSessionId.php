<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\RegenerateSessionId;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class ReplaceCurrentSessionId
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $newId) : void
    {
        $this->store->put(key: '_session_id', value: $newId);
    }
}