<?php

declare(strict_types=1);

namespace components\HTTP\Session\ImportSessionState;

use components\HTTP\Session\SessionStore\SessionStore;

final class ReplaceImportedSessionState
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(array $data) : void
    {
        $this->store->flush();

        foreach ($data as $key => $value) {
            $this->store->put(key: $key, value: $value);
        }
    }
}