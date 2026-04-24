<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RestoreSessionSnapshot;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class ReadSessionSnapshot
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(string $name) : ?array
    {
        $key = '_snapshot.' . $name;

        return $this->store->get($key);
    }
}