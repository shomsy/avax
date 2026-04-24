<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RestoreSessionSnapshot;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class ReplaceSessionStateFromSnapshot
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(array $snapshot) : void
    {
        $this->store->flush();

        foreach ($snapshot['data'] as $key => $value) {
            $this->store->put($key, $value);
        }
    }
}